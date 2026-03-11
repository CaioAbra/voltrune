<?php

namespace App\Modules\Solar\Services;

use App\Modules\Solar\Models\SolarProject;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class SolarRadiationService
{
    private const PVGIS_ENDPOINT = 'https://re.jrc.ec.europa.eu/api/PVcalc';
    private const CACHE_TTL_SECONDS = 2592000;

    /**
     * @return array{factor: float, source: string, status: string, fetched_at: ?Carbon, message: ?string}
     */
    public function resolveFactorForProject(?SolarProject $project): array
    {
        $defaultFactor = SolarSizingService::DEFAULT_SOLAR_FACTOR;

        if (! $project instanceof SolarProject) {
            return [
                'factor' => $defaultFactor,
                'source' => 'fallback',
                'status' => 'fallback',
                'fetched_at' => null,
                'message' => 'Fator padrao ativo ate haver coordenadas disponiveis.',
            ];
        }

        if (
            $project->solar_factor_used !== null
            && (float) $project->solar_factor_used > 0
            && $project->solar_factor_fetched_at !== null
        ) {
            return [
                'factor' => (float) $project->solar_factor_used,
                'source' => $project->solar_factor_source ?: 'fallback',
                'status' => $project->radiation_status ?: 'ready',
                'fetched_at' => $project->solar_factor_fetched_at,
                'message' => null,
            ];
        }

        $latitude = $project->latitude !== null ? (float) $project->latitude : null;
        $longitude = $project->longitude !== null ? (float) $project->longitude : null;

        if ($latitude === null || $longitude === null) {
            return [
                'factor' => $this->fallbackFactor($project),
                'source' => $project->solar_factor_source ?: 'fallback',
                'status' => $project->radiation_status ?: 'fallback',
                'fetched_at' => $project->solar_factor_fetched_at,
                'message' => 'Sem latitude/longitude disponiveis. O Solar esta usando o fator padrao.',
            ];
        }

        return $this->fetchRegionalFactor($latitude, $longitude, $defaultFactor);
    }

    public function refreshProjectRadiationData(SolarProject $project): SolarProject
    {
        $factorData = $this->resolveFactorForProject($project);

        $project->forceFill([
            'solar_factor_used' => $factorData['factor'],
            'solar_factor_source' => $factorData['source'],
            'solar_factor_fetched_at' => $factorData['fetched_at'],
            'radiation_status' => $factorData['status'],
        ])->save();

        return $project->refresh();
    }

    private function fallbackFactor(SolarProject $project): float
    {
        if (
            $project->solar_factor_used !== null
            && (float) $project->solar_factor_used > 0
            && $project->solar_factor_source === 'pvgis'
        ) {
            return (float) $project->solar_factor_used;
        }

        return SolarSizingService::DEFAULT_SOLAR_FACTOR;
    }

    /**
     * @return array{factor: float, source: string, status: string, fetched_at: ?Carbon, message: ?string}
     */
    private function fetchRegionalFactor(float $latitude, float $longitude, float $defaultFactor): array
    {
        $cacheKey = sprintf('solar:pvgis:%s:%s', round($latitude, 4), round($longitude, 4));

        try {
            /** @var array{factor: float, source: string, status: string, fetched_at: string} $cached */
            $cached = Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($latitude, $longitude): array {
                $response = Http::acceptJson()
                    ->timeout(10)
                    ->retry(1, 300)
                    ->get(self::PVGIS_ENDPOINT, [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'peakpower' => 1,
                        'loss' => 14,
                        'optimalangles' => 1,
                        'outputformat' => 'json',
                    ])
                    ->throw()
                    ->json();

                $monthlyFactor = data_get($response, 'outputs.totals.fixed.E_m');

                if (! is_numeric($monthlyFactor)) {
                    $monthlySeries = data_get($response, 'outputs.monthly.fixed');

                    if (is_array($monthlySeries) && count($monthlySeries) > 0) {
                        $values = collect($monthlySeries)
                            ->pluck('E_m')
                            ->filter(fn ($value) => is_numeric($value))
                            ->map(fn ($value) => (float) $value)
                            ->values();

                        $monthlyFactor = $values->isNotEmpty() ? $values->avg() : null;
                    }
                }

                if (! is_numeric($monthlyFactor) || (float) $monthlyFactor <= 0) {
                    throw new \RuntimeException('PVGIS nao retornou fator mensal valido.');
                }

                return [
                    'factor' => round((float) $monthlyFactor, 2),
                    'source' => 'pvgis',
                    'status' => 'ready',
                    'fetched_at' => now()->toISOString(),
                ];
            });

            return [
                'factor' => (float) $cached['factor'],
                'source' => $cached['source'],
                'status' => $cached['status'],
                'fetched_at' => Carbon::parse($cached['fetched_at']),
                'message' => null,
            ];
        } catch (Throwable) {
            return [
                'factor' => $defaultFactor,
                'source' => 'fallback',
                'status' => 'fallback',
                'fetched_at' => now(),
                'message' => 'PVGIS indisponivel no momento. O Solar voltou para o fator padrao.',
            ];
        }
    }
}
