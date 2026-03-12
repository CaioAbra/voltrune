<?php

namespace Tests\Unit;

use App\Modules\Solar\Controllers\ProjectController;
use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Services\EnergyUtilityResolverService;
use App\Modules\Solar\Services\SolarGeocodingService;
use App\Modules\Solar\Services\SolarNavigationService;
use App\Modules\Solar\Services\SolarRadiationService;
use App\Modules\Solar\Services\SolarSimulationService;
use App\Modules\Solar\Services\SolarSizingService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class ProjectControllerLocationPreparationTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_preserves_geocoding_status_when_coordinates_do_not_need_refresh(): void
    {
        $navigation = Mockery::mock(SolarNavigationService::class);
        $sizing = Mockery::mock(SolarSizingService::class);
        $utilityResolver = Mockery::mock(EnergyUtilityResolverService::class);
        $geocoding = Mockery::mock(SolarGeocodingService::class);
        $radiation = Mockery::mock(SolarRadiationService::class);
        $simulations = Mockery::mock(SolarSimulationService::class);

        $controller = new ProjectController(
            $navigation,
            $sizing,
            $utilityResolver,
            $geocoding,
            $radiation,
            $simulations,
        );

        $project = new SolarProject([
            'latitude' => -23.5648865,
            'longitude' => -46.6519180,
            'geocoding_status' => 'ready',
            'geocoding_precision' => 'address',
            'solar_factor_used' => 145.2,
            'solar_factor_source' => 'pvgis',
            'radiation_status' => 'ready',
        ]);

        $geocoding->shouldReceive('shouldRefreshCoordinates')
            ->once()
            ->andReturnFalse();

        $geocoding->shouldReceive('normalizePrecision')
            ->never();

        $utilityResolver->shouldReceive('resolveUtilityByCity')
            ->once()
            ->with('Sao Paulo', 'SP')
            ->andReturn(null);

        $method = new \ReflectionMethod($controller, 'prepareLocationData');
        $method->setAccessible(true);

        $data = $method->invoke($controller, [
            'zip_code' => '01310-100',
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'district' => 'Bela Vista',
            'city' => 'Sao Paulo',
            'state' => 'sp',
            'energy_utility_id' => null,
            'pricing_notes' => null,
            'inverter_model' => null,
            'connection_type' => 'bi',
        ], $project);

        $this->assertSame(-23.5648865, (float) $data['latitude']);
        $this->assertSame(-46.6519180, (float) $data['longitude']);
        $this->assertSame('ready', $data['geocoding_status']);
        $this->assertSame('address', $data['geocoding_precision']);
    }
}
