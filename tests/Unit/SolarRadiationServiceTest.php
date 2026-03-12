<?php

namespace Tests\Unit;

use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Services\SolarRadiationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SolarRadiationServiceTest extends TestCase
{
    public function test_it_fetches_and_returns_pvgis_factor_when_coordinates_are_available(): void
    {
        Cache::flush();
        Http::fake([
            '*' => Http::response([
                'outputs' => [
                    'totals' => [
                        'fixed' => [
                            'E_m' => 154.37,
                        ],
                    ],
                ],
            ]),
        ]);

        $project = new SolarProject([
            'latitude' => -23.5505,
            'longitude' => -46.6333,
        ]);

        $service = new SolarRadiationService();
        $factorData = $service->resolveFactorForProject($project);

        $this->assertSame(154.37, $factorData['factor']);
        $this->assertSame('pvgis', $factorData['source']);
        $this->assertSame('ready', $factorData['status']);
        $this->assertNotNull($factorData['fetched_at']);
        Http::assertSentCount(1);
    }

    public function test_it_uses_saved_project_factor_without_calling_pvgis_again(): void
    {
        Http::fake();

        $project = new SolarProject([
            'latitude' => -23.5505,
            'longitude' => -46.6333,
            'solar_factor_used' => 148.42,
            'solar_factor_source' => 'pvgis',
            'solar_factor_fetched_at' => Carbon::parse('2026-03-11 10:00:00'),
            'radiation_status' => 'ready',
        ]);

        $service = new SolarRadiationService();
        $factorData = $service->resolveFactorForProject($project);

        $this->assertSame(148.42, $factorData['factor']);
        $this->assertSame('pvgis', $factorData['source']);
        $this->assertSame('ready', $factorData['status']);
        Http::assertNothingSent();
    }

    public function test_it_marks_fallback_when_pvgis_fails(): void
    {
        Http::fake([
            '*' => Http::response([], 500),
        ]);

        $project = new SolarProject([
            'latitude' => -23.5505,
            'longitude' => -46.6333,
        ]);

        $service = new SolarRadiationService();
        $factorData = $service->resolveFactorForProject($project);

        $this->assertSame(130.0, $factorData['factor']);
        $this->assertSame('fallback', $factorData['source']);
        $this->assertSame('fallback', $factorData['status']);
        $this->assertNotNull($factorData['fetched_at']);
        Http::assertSentCount(1);
    }
}
