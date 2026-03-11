<?php

namespace App\Modules\Solar\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class SolarGeocodingService
{
    private const NOMINATIM_ENDPOINT = 'https://nominatim.openstreetmap.org/search';

    /**
     * @param array<string, mixed> $data
     * @return array{latitude: ?float, longitude: ?float, status: string}
     */
    public function resolveCoordinates(array $data): array
    {
        if (! $this->hasMinimumAddress($data)) {
            return [
                'latitude' => null,
                'longitude' => null,
                'status' => empty($data['zip_code']) ? 'not_requested' : 'address_loaded',
            ];
        }

        foreach ($this->queries($data) as $query) {
            $result = $this->search($query);

            if ($result !== null) {
                return [
                    'latitude' => $result['latitude'],
                    'longitude' => $result['longitude'],
                    'status' => 'ready',
                ];
            }
        }

        return [
            'latitude' => null,
            'longitude' => null,
            'status' => 'not_found',
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function shouldRefreshCoordinates(array $data, ?string $previousAddress, bool $coordinatesMissing = false): bool
    {
        $currentAddress = $this->addressFingerprint($data);

        if ($currentAddress === '') {
            return false;
        }

        if ($coordinatesMissing) {
            return true;
        }

        return $previousAddress === null || trim($previousAddress) !== $currentAddress;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function addressFingerprint(array $data): string
    {
        return collect([
            $data['zip_code'] ?? null,
            $data['street'] ?? null,
            $data['number'] ?? null,
            $data['district'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
        ])->filter(fn ($value) => $value !== null && trim((string) $value) !== '')
            ->map(fn ($value) => trim((string) $value))
            ->implode(' | ');
    }

    /**
     * @param array<string, mixed> $data
     */
    private function hasMinimumAddress(array $data): bool
    {
        return ! empty($data['zip_code'])
            && ! empty($data['street'])
            && ! empty($data['number'])
            && ! empty($data['city'])
            && ! empty($data['state']);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<int, array<string, scalar>>
     */
    private function queries(array $data): array
    {
        $street = trim(sprintf('%s %s', $data['number'] ?? '', $data['street'] ?? ''));

        return [
            [
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
            [
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
