<?php

namespace Tests\Unit;

use App\Modules\Solar\Services\SolarSizingService;
use Tests\TestCase;

class SolarSizingServiceTest extends TestCase
{
    public function test_it_prioritizes_saved_project_factor_when_applying_sizing(): void
    {
        $service = new SolarSizingService();

        $data = $service->applySuggestedSizing([
            'monthly_consumption_kwh' => 600,
            'module_power' => 550,
            'solar_factor_used' => 150,
        ]);

        $this->assertSame(150.0, $data['solar_factor_used']);
        $this->assertSame(4.0, $data['system_power_kwp']);
        $this->assertSame(8, $data['module_quantity']);
        $this->assertSame(600.0, $data['estimated_generation_kwh']);
    }

    public function test_it_falls_back_to_default_factor_only_when_project_factor_is_missing(): void
    {
        $service = new SolarSizingService();

        $data = $service->applySuggestedSizing([
            'monthly_consumption_kwh' => 650,
            'module_power' => 550,
        ]);

        $this->assertSame(130.0, $data['solar_factor_used']);
        $this->assertSame(5.0, $data['system_power_kwp']);
        $this->assertSame(10, $data['module_quantity']);
        $this->assertSame(650.0, $data['estimated_generation_kwh']);
    }

    public function test_it_uses_regional_price_when_company_price_is_missing(): void
    {
        $service = new SolarSizingService();

        $context = $service->resolveContextualPricePerKwp(null, 'SP');

        $this->assertSame('regional', $context['source']);
        $this->assertSame(4310.0, $context['value']);
    }

    public function test_it_uses_national_fallback_when_company_and_region_are_missing(): void
    {
        $service = new SolarSizingService();

        $context = $service->resolveContextualPricePerKwp(null, 'XX');

        $this->assertSame('fallback', $context['source']);
        $this->assertSame(4200.0, $context['value']);
        $this->assertSame('BR', $context['market_defaults']['state']);
    }

    public function test_it_estimates_area_and_payback(): void
    {
        $service = new SolarSizingService();

        $this->assertSame(22.5, $service->estimateAreaSquareMeters(5));
        $this->assertSame(18.4, $service->estimateAreaFromModules(8));
        $this->assertSame(40, $service->estimatePaybackMonths(12000, 370));
        $this->assertSame(3.3, $service->estimatePaybackYears(12000, 370));
        $this->assertSame(30.0, $service->estimateRoiPercentage(12000, 370));
    }

    public function test_it_estimates_kit_cost_breakdown_and_gross_profit(): void
    {
        $service = new SolarSizingService();

        $kitCost = $service->estimateKitCost(15000, 25);
        $breakdown = $service->estimateKitCostBreakdown($kitCost);

        $this->assertSame(12000.0, $kitCost);
        $this->assertSame(6000.0, $breakdown['modules']);
        $this->assertSame(2400.0, $breakdown['inverter']);
        $this->assertSame(1440.0, $breakdown['structure']);
        $this->assertSame(2160.0, $breakdown['installation']);
        $this->assertSame(3000.0, $service->estimateGrossProfit(15000, $kitCost));
    }

    public function test_it_builds_a_basic_system_composition(): void
    {
        $service = new SolarSizingService();

        $composition = $service->resolveSystemComposition(8, 550, 'WEG SIW300H', 4.4);

        $this->assertCount(4, $composition);
        $this->assertSame('Modulos fotovoltaicos', $composition[0]['label']);
        $this->assertSame('8 modulos de 550 W', $composition[0]['detail']);
        $this->assertSame('Inversor', $composition[1]['label']);
        $this->assertSame('WEG SIW300H', $composition[1]['detail']);
    }

    public function test_it_exposes_a_blueprint_for_future_equipment_catalog(): void
    {
        $service = new SolarSizingService();

        $blueprint = $service->equipmentBlueprint();

        $this->assertArrayHasKey('modules', $blueprint);
        $this->assertSame('module', $blueprint['modules']['category']);
        $this->assertArrayHasKey('inverter', $blueprint);
        $this->assertSame('Inversor', $blueprint['inverter']['label']);
    }
}
