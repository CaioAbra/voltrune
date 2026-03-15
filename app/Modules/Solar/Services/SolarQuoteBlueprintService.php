<?php

namespace App\Modules\Solar\Services;

use App\Modules\Solar\Models\SolarSimulation;

class SolarQuoteBlueprintService
{
    /**
     * @return array<string, mixed>
     */
    public function buildSimulationSnapshot(SolarSimulation $simulation): array
    {
        return [
            'name' => $simulation->name,
            'status' => $simulation->status,
            'system_power_kwp' => $simulation->system_power_kwp !== null ? (float) $simulation->system_power_kwp : null,
            'module_power' => $simulation->module_power,
            'module_quantity' => $simulation->module_quantity,
            'estimated_generation_kwh' => $simulation->estimated_generation_kwh !== null ? (float) $simulation->estimated_generation_kwh : null,
            'suggested_price' => $simulation->suggested_price !== null ? (float) $simulation->suggested_price : null,
            'estimated_monthly_savings' => $simulation->estimated_monthly_savings !== null ? (float) $simulation->estimated_monthly_savings : null,
            'estimated_payback_months' => $simulation->estimated_payback_months,
            'inverter_model' => $simulation->inverter_model,
            'solar_factor_used' => $simulation->solar_factor_used !== null ? (float) $simulation->solar_factor_used : null,
        ];
    }

    /**
     * @return array{final_price: ?float, total_value: ?float, estimated_savings: ?float, payback_months: ?int}
     */
    public function resolveBaseTotals(?array $snapshot = null, ?SolarSimulation $simulation = null): array
    {
        $suggestedPrice = $snapshot['suggested_price'] ?? $simulation?->suggested_price;
        $estimatedSavings = $snapshot['estimated_monthly_savings'] ?? $simulation?->estimated_monthly_savings;
        $paybackMonths = $snapshot['estimated_payback_months'] ?? $simulation?->estimated_payback_months;

        return [
            'final_price' => $suggestedPrice !== null ? round((float) $suggestedPrice, 2) : null,
            'total_value' => $suggestedPrice !== null ? round((float) $suggestedPrice, 2) : null,
            'estimated_savings' => $estimatedSavings !== null ? round((float) $estimatedSavings, 2) : null,
            'payback_months' => $paybackMonths !== null ? (int) $paybackMonths : null,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function buildSeedItemsFromSimulation(SolarSimulation $simulation): array
    {
        $kitCost = $simulation->estimated_kit_cost !== null ? (float) $simulation->estimated_kit_cost : 0.0;
        $suggestedPrice = $simulation->suggested_price !== null ? (float) $simulation->suggested_price : 0.0;
        $composition = is_array($simulation->system_composition_json) ? array_values($simulation->system_composition_json) : [];

        $components = [
            [
                'type' => 'material',
                'category' => 'module',
                'name' => 'Modulos fotovoltaicos',
                'description' => $composition[0]['detail'] ?? 'Quantidade e potencia definidas automaticamente.',
                'quantity' => max((int) ($simulation->module_quantity ?: 1), 1),
                'total_cost' => $simulation->estimated_module_cost !== null ? (float) $simulation->estimated_module_cost : 0.0,
            ],
            [
                'type' => 'material',
                'category' => 'inverter',
                'name' => 'Inversor',
                'description' => $composition[1]['detail'] ?? ($simulation->inverter_model ?: 'Inversor compativel com o sistema sugerido.'),
                'quantity' => 1,
                'total_cost' => $simulation->estimated_inverter_cost !== null ? (float) $simulation->estimated_inverter_cost : 0.0,
            ],
            [
                'type' => 'material',
                'category' => 'structure',
                'name' => 'Estrutura',
                'description' => $composition[2]['detail'] ?? 'Estrutura de fixacao compativel com o local da instalacao.',
                'quantity' => 1,
                'total_cost' => $simulation->estimated_structure_cost !== null ? (float) $simulation->estimated_structure_cost : 0.0,
            ],
            [
                'type' => 'service',
                'category' => 'installation',
                'name' => 'Instalacao e itens basicos',
                'description' => $composition[3]['detail'] ?? 'Cabos, conectores, protecoes eletricas e mao de obra basica.',
                'quantity' => 1,
                'total_cost' => $simulation->estimated_installation_cost !== null ? (float) $simulation->estimated_installation_cost : 0.0,
            ],
        ];

        $seedItems = [];
        $runningPrice = 0.0;
        $lastIndex = array_key_last($components);

        foreach ($components as $index => $component) {
            $totalCost = round((float) $component['total_cost'], 2);
            $quantity = max((float) $component['quantity'], 1);

            if ($totalCost <= 0 && $suggestedPrice <= 0) {
                continue;
            }

            if ($suggestedPrice > 0 && $kitCost > 0 && $totalCost > 0) {
                $totalPrice = $index === $lastIndex
                    ? round(max($suggestedPrice - $runningPrice, 0), 2)
                    : round($suggestedPrice * ($totalCost / $kitCost), 2);
            } else {
                $totalPrice = $totalCost;
            }

            $runningPrice += $totalPrice;

            $seedItems[] = [
                'type' => $component['type'],
                'category' => $component['category'],
                'name' => $component['name'],
                'description' => $component['description'],
                'quantity' => $quantity,
                'unit_cost' => round($totalCost / $quantity, 2),
                'unit_price' => round($totalPrice / $quantity, 2),
                'total_cost' => $totalCost,
                'total_price' => $totalPrice,
            ];
        }

        return $seedItems;
    }
}
