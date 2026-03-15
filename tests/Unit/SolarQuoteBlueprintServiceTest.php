<?php

namespace Tests\Unit;

use App\Modules\Solar\Models\SolarSimulation;
use App\Modules\Solar\Services\SolarQuoteBlueprintService;
use Tests\TestCase;

class SolarQuoteBlueprintServiceTest extends TestCase
{
    public function test_it_builds_a_stable_snapshot_and_base_totals_from_simulation(): void
    {
        $simulation = new SolarSimulation([
            'name' => 'Simulacao premium',
            'status' => 'proposal',
            'system_power_kwp' => 6.24,
            'module_power' => 550,
            'module_quantity' => 12,
            'estimated_generation_kwh' => 812.4,
            'suggested_price' => 26890,
            'estimated_monthly_savings' => 730,
            'estimated_payback_months' => 37,
            'inverter_model' => 'WEG SIW500',
            'solar_factor_used' => 130.2,
        ]);

        $service = new SolarQuoteBlueprintService();
        $snapshot = $service->buildSimulationSnapshot($simulation);
        $baseTotals = $service->resolveBaseTotals($snapshot, $simulation);

        $this->assertSame('Simulacao premium', $snapshot['name']);
        $this->assertSame(6.24, $snapshot['system_power_kwp']);
        $this->assertSame(26890.0, $baseTotals['final_price']);
        $this->assertSame(730.0, $baseTotals['estimated_savings']);
        $this->assertSame(37, $baseTotals['payback_months']);
    }

    public function test_it_builds_seed_items_that_preserve_the_simulation_total(): void
    {
        $simulation = new SolarSimulation([
            'module_quantity' => 8,
            'suggested_price' => 15000,
            'estimated_kit_cost' => 12000,
            'estimated_module_cost' => 6000,
            'estimated_inverter_cost' => 2400,
            'estimated_structure_cost' => 1440,
            'estimated_installation_cost' => 2160,
            'system_composition_json' => [
                ['detail' => '8 modulos de 550 W'],
                ['detail' => 'WEG SIW300H'],
                ['detail' => 'Estrutura compativel com o telhado'],
                ['detail' => 'Cabos, conectores e mao de obra basica'],
            ],
        ]);

        $service = new SolarQuoteBlueprintService();
        $items = $service->buildSeedItemsFromSimulation($simulation);

        $this->assertCount(4, $items);
        $this->assertSame('material', $items[0]['type']);
        $this->assertSame('module', $items[0]['category']);
        $this->assertSame(8.0, $items[0]['quantity']);
        $this->assertSame(15000.0, round(array_sum(array_column($items, 'total_price')), 2));
    }
}
