<?php

namespace Tests\Unit;

use App\Modules\Solar\Models\SolarCompanySetting;
use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Services\SolarSimulationService;
use App\Modules\Solar\Services\SolarSizingService;
use Tests\TestCase;

class SolarSimulationServiceTest extends TestCase
{
    public function test_it_builds_a_simulation_snapshot_from_project_context(): void
    {
        $project = new SolarProject([
            'company_id' => 1,
            'monthly_consumption_kwh' => 420,
            'energy_bill_value' => 410,
            'system_power_kwp' => 3.41,
            'module_power' => 550,
            'module_quantity' => 7,
            'inverter_model' => 'Solis 3kW',
            'estimated_generation_kwh' => 399.65,
            'solar_factor_used' => 117.2,
            'solar_factor_source' => 'pvgis',
            'suggested_price' => 14322,
            'status' => 'draft',
            'notes' => 'Projeto base para simulacao.',
        ]);
        $setting = new SolarCompanySetting([
            'default_module_power' => 550,
            'default_inverter_model' => 'Solis 3kW',
            'price_per_kwp' => 4200,
        ]);

        $service = new SolarSimulationService(new SolarSizingService());
        $payload = $service->buildPayloadFromProject($project, $setting);

        $this->assertSame(3.41, (float) $payload['system_power_kwp']);
        $this->assertSame(550, $payload['module_power']);
        $this->assertSame(7, $payload['module_quantity']);
        $this->assertSame(399.65, (float) $payload['estimated_generation_kwh']);
        $this->assertSame(14322.0, (float) $payload['suggested_price']);
        $this->assertSame(5869.67, (float) $payload['estimated_module_cost']);
        $this->assertSame(2347.87, (float) $payload['estimated_inverter_cost']);
        $this->assertSame(1408.72, (float) $payload['estimated_structure_cost']);
        $this->assertSame(2113.08, (float) $payload['estimated_installation_cost']);
        $this->assertSame(11739.34, (float) $payload['estimated_kit_cost']);
        $this->assertSame(2582.66, (float) $payload['estimated_gross_profit']);
        $this->assertSame(340.0, (float) $payload['estimated_monthly_savings']);
        $this->assertSame(4080.0, (float) $payload['estimated_annual_savings']);
        $this->assertSame('pvgis', $payload['solar_factor_source']);
        $this->assertSame('draft', $payload['status']);
        $this->assertCount(4, $payload['system_composition_json']);
    }
}
