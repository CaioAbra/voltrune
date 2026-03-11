<?php

namespace App\Modules\Solar\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class SolarGeocodingService
{
    private const NOMINATIM_ENDPOINT = 'https://nominatim.openstreetmap.org/search';
    private const PRECISION_FALLBACK = 'fallback';
    private const PRECISION_CITY = 'city';
    private const PRECISION_ADDRESS = 'address';

    /**
     * @param array<string, mixed> $data
     * @param array{latitude: ?float, longitude: ?float}|null $currentCoordinates
     * @return array{latitude: ?float, longitude: ?float, status: string, precision: string}
     */
    public function resolveCoordinates(array $data, ?array $currentCoordinates = null, ?string $currentPrecision = null): array
    {
        if (! $this->hasMinimumCityContext($data)) {
            return [
                'latitude' => $currentCoordinates['latitude'] ?? null,
                'longitude' => $currentCoordinates['longitude'] ?? null,
                'status' => empty($data['zip_code']) ? 'not_requested' : 'address_loaded',
                'precision' => $this->normalizePrecision($currentPrecision),
            ];
        }

        foreach ($this->queries($data) as $query) {
            $result = $this->search($query['params']);

            if ($result !== null) {
                return [
                    'latitude' => $result['latitude'],
                    'longitude' => $result['longitude'],
                    'status' => 'ready',
                    'precision' => $query['precision'],
                ];
            }
        }

        if (
            $currentCoordinates !== null
            && $currentCoordinates['latitude'] !== null
            && $currentCoordinates['longitude'] !== null
            && in_array($this->normalizePrecision($currentPrecision), [self::PRECISION_CITY, self::PRECISION_ADDRESS], true)
        ) {
            return [
                'latitude' => $currentCoordinates['latitude'],
                'longitude' => $currentCoordinates['longitude'],
                'status' => 'ready',
                'precision' => $this->normalizePrecision($currentPrecision),
            ];
        }

        return [
            'latitude' => null,
            'longitude' => null,
            'status' => 'not_found',
            'precision' => self::PRECISION_FALLBACK,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function shouldRefreshCoordinates(
        array $data,
        ?array $currentData = null,
        bool $coordinatesMissing = false,
        ?string $currentPrecision = null,
    ): bool {
        if (! $this->hasMinimumCityContext($data)) {
            return false;
        }

        $desiredPrecision = $this->resolveDesiredPrecision($data);
        $normalizedCurrentPrecision = $this->normalizePrecision($currentPrecision);
        $nextFingerprint = $this->fingerprintForPrecision($data, $desiredPrecision);
        $currentFingerprint = $currentData !== null
            ? $this->fingerprintForPrecision($currentData, $desiredPrecision)
            : '';

        if ($nextFingerprint === '') {
            return false;
        }

        if ($coordinatesMissing) {
            return true;
        }

        if ($this->precisionRank($desiredPrecision) > $this->precisionRank($normalizedCurrentPrecision)) {
            return true;
        }

        return $currentFingerprint !== $nextFingerprint;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function resolveDesiredPrecision(array $data): string
    {
        if ($this->hasAddressPrecisionContext($data)) {
            return self::PRECISION_ADDRESS;
        }

        if ($this->hasMinimumCityContext($data)) {
            return self::PRECISION_CITY;
        }

        return self::PRECISION_FALLBACK;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function addressFingerprint(array $data): string
    {
        return $this->fingerprintForPrecision($data, self::PRECISION_ADDRESS);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function cityFingerprint(array $data): string
    {
        return $this->fingerprintForPrecision($data, self::PRECISION_CITY);
    }

    public function normalizePrecision(?string $precision): string
    {
        return in_array($precision, [self::PRECISION_CITY, self::PRECISION_ADDRESS], true)
            ? $precision
            : self::PRECISION_FALLBACK;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function fingerprintForPrecision(array $data, string $precision): string
    {
        $parts = match ($precision) {
            self::PRECISION_ADDRESS => [
                $data['zip_code'] ?? null,
                $data['street'] ?? null,
                $data['number'] ?? null,
                $data['district'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
            ],
            self::PRECISION_CITY => [
                $data['zip_code'] ?? null,
                $data['city'] ?? null,
                $data['state'] ?? null,
            ],
            default => [],
        };

        return collect($parts)
            ->filter(fn ($value) => $value !== null && trim((string) $value) !== '')
            ->map(fn ($value) => trim((string) $value))
            ->implode(' | ');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hasMinimumCityContext(array $data): bool
    {
        return ! empty($data['city'])
            && ! empty($data['state']);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hasAddressPrecisionContext(array $data): bool
    {
        return ! empty($data['street'])
            && ! empty($data['number'])
            && ! empty($data['city'])
            && ! empty($data['state']);
    }

    private function precisionRank(string $precision): int
    {
        return match ($precision) {
            self::PRECISION_ADDRESS => 2,
            self::PRECISION_CITY => 1,
            default => 0,
        };
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array{precision: string, params: array<string, scalar>}>
     */
    private function queries(array $data): array
    {
        $street = trim(sprintf('%s %s', $data['number'] ?? '', $data['street'] ?? ''));
        $queries = [];

        if ($this->hasAddressPrecisionContext($data)) {
            $queries[] = [
                'precision' => self::PRECISION_ADDRESS,
                'params' => [
                    'street' => $street,
                    'city' => (string) ($data['city'] ?? ''),
                    'state' => (string) ($data['state'] ?? ''),
                    'postalcode' => (string) ($data['zip_code'] ?? ''),
                    'country' => 'Brasil',
                    'countrycodes' => 'br',
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'addressdetails' => 1,
                ],
            ];
            $queries[] = [
                'precision' => self::PRECISION_ADDRESS,
                'params' => [
                    'q' => implode(', ', array_filter([
                        $street,
                        $data['district'] ?? null,
                        $data['city'] ?? null,
                        $data['state'] ?? null,
                        $data['zip_code'] ?? null,
                        'Brasil',
                    ])),
                    'countrycodes' => 'br',
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'addressdetails' => 1,
                ],
            ];
        }

        $queries[] = [
            'precision' => self::PRECISION_CITY,
            'params' => [
                'city' => (string) ($data['city'] ?? ''),
                'state' => (string) ($data['state'] ?? ''),
                'postalcode' => (string) ($data['zip_code'] ?? ''),
                'country' => 'Brasil',
                'countrycodes' => 'br',
                'format' => 'jsonv2',
                'limit' => 1,
                'addressdetails' => 1,
            ],
        ];
        $queries[] = [
            'precision' => self::PRECISION_CITY,
            'params' => [
                'q' => implode(', ', array_filter([
                    $data['city'] ?? null,
                    $data['state'] ?? null,
                    $data['zip_code'] ?? null,
                    'Brasil',
                ])),
                'countrycodes' => 'br',
                'format' => 'jsonv2',
                'limit' => 1,
                'addressdetails' => 1,
            ],
        ];

        return $queries;
    }

    /**
     * @param array<string, scalar> $query
     * @return array{latitude: float, longitude: float}|null
     */
    private function search(array $query): ?array
    {
        try {
            $response = Http::acceptJson()
                ->timeout(10)
                ->retry(1, 300)
                ->withHeaders([
                    'User-Agent' => 'VoltruneSolar/1.0',
                ])
                ->get(self::NOMINATIM_ENDPOINT, $query)
                ->throw()
                ->json();

            if (! is_array($response) || empty($response[0])) {
                return null;
            }

            $latitude = data_get($response, '0.lat');
            $longitude = data_get($response, '0.lon');

            if (! is_numeric($latitude) || ! is_numeric($longitude)) {
                return null;
            }

            return [
                'latitude' => round((float) $latitude, 7),
                'longitude' => round((float) $longitude, 7),
            ];
        } catch (Throwable) {
            return null;
        }
    }
}
