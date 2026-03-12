<?php

namespace App\Modules\Solar\Services;

use App\Modules\Solar\Models\SolarCompanySetting;
use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Models\SolarSimulation;

class SolarSimulationService
{
    public function __construct(
        private readonly SolarSizingService $sizing,
    ) {
    }

    public function syncDefaultForProject(SolarProject $project, ?SolarCompanySetting $companySetting = null): SolarSimulation
    {
        $simulation = $project->simulations()
            ->orderBy('id')
            ->first();

        if (! $simulation instanceof SolarSimulation) {
            $simulation = new SolarSimulation([
                'company_id' => $project->company_id,
                'name' => 'Simulacao padrao',
            ]);
            $simulation->project()->associate($project);
        }

        $simulation->fill($this->buildPayloadFromProject($project, $companySetting));
        $simulation->save();

        return $simulation->refresh();
    }

    public function createSnapshotForProject(
        SolarProject $project,
        ?SolarCompanySetting $companySetting = null,
        ?string $name = null,
        ?string $notes = null,
    ): SolarSimulation {
        $simulation = new SolarSimulation([
            'company_id' => $project->company_id,
            'name' => $this->resolveSnapshotName($project, $name),
        ]);

        $simulation->project()->associate($project);
        $simulation->fill($this->buildPayloadFromProject($project, $companySetting));

        if ($notes !== null && trim($notes) !== '') {
            $simulation->notes = trim($notes);
        }

        $simulation->save();

        return $simulation->refresh();
    }

    public function duplicate(SolarSimulation $simulation): SolarSimulation
    {
        $duplicate = $simulation->replicate([
            'created_at',
            'updated_at',
        ]);

        $duplicate->name = $this->resolveDuplicateName($simulation);
        $duplicate->status = 'draft';
        $duplicate->save();

        return $duplicate->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPayloadFromProject(SolarProject $project, ?SolarCompanySetting $companySetting = null): array
    {
        $systemPower = $project->system_power_kwp
            ?: $this->sizing->estimateRequiredPowerKwp($project->monthly_consumption_kwh, $project->solar_factor_used);
        $modulePower = $project->module_power ?: ($companySetting?->default_module_power ?: 550);
        $moduleQuantity = $project->module_quantity
            ?: $this->sizing->estimateModuleQuantity($systemPower, $modulePower);
        $generation = $project->estimated_generation_kwh
            ?: $this->sizing->estimateGenerationKwh($systemPower, $project->solar_factor_used);
        $price = $project->suggested_price;

        if ($price === null && $systemPower !== null) {
            $priceContext = $this->sizing->resolveContextualPricePerKwp($companySetting?->price_per_kwp, $project->state);
            $price = $this->sizing->estimateSuggestedPrice($systemPower, $priceContext['value']);
        }

        $inverterModel = $project->inverter_model ?: $companySetting?->default_inverter_model;
        $monthlySavings = $this->sizing->estimateMonthlySavings($project->energy_bill_value);
        $annualSavings = $this->sizing->estimateAnnualSavings($project->energy_bill_value);
        $lifetimeSavings = $this->sizing->estimateLifetimeSavings($project->energy_bill_value);
        $areaEstimated = $this->sizing->estimateAreaFromModules($moduleQuantity)
            ?: $this->sizing->estimateAreaSquareMeters($systemPower);
        $kitCost = $this->sizing->estimateKitCost($price, $companySetting?->margin_percent);
        $kitBreakdown = $this->sizing->estimateKitCostBreakdown($kitCost);

        return [
            'system_power_kwp' => $systemPower,
            'module_power' => $modulePower,
            'module_quantity' => $moduleQuantity,
            'estimated_generation_kwh' => $generation,
            'area_estimated' => $areaEstimated,
            'inverter_model' => $inverterModel,
            'solar_factor_used' => $project->solar_factor_used,
            'solar_factor_source' => $project->solar_factor_source,
            'suggested_price' => $price,
            'estimated_module_cost' => $kitBreakdown['modules'],
            'estimated_inverter_cost' => $kitBreakdown['inverter'],
            'estimated_structure_cost' => $kitBreakdown['structure'],
            'estimated_installation_cost' => $kitBreakdown['installation'],
            'estimated_kit_cost' => $kitCost,
            'estimated_gross_profit' => $this->sizing->estimateGrossProfit($price, $kitCost),
            'estimated_monthly_savings' => $monthlySavings,
            'estimated_annual_savings' => $annualSavings,
            'estimated_lifetime_savings' => $lifetimeSavings,
            'estimated_roi' => $this->sizing->estimateRoiPercentage($price, $project->energy_bill_value),
            'estimated_payback_months' => $this->sizing->estimatePaybackMonths($price, $project->energy_bill_value),
            'system_composition_json' => $this->sizing->resolveSystemComposition(
                $moduleQuantity,
                $modulePower,
                $inverterModel,
                $systemPower,
            ),
            'status' => $project->status ?: 'draft',
            'notes' => $project->notes,
        ];
    }

    private function resolveSnapshotName(SolarProject $project, ?string $name): string
    {
        $customName = trim((string) $name);

        if ($customName !== '') {
            return $customName;
        }

        $nextPosition = $project->simulations()->count() + 1;

        return match ($nextPosition) {
            1 => 'Simulacao padrao',
            2 => 'Simulacao ampliada',
            3 => 'Simulacao premium',
            default => 'Simulacao ' . $nextPosition,
        };
    }

    private function resolveDuplicateName(SolarSimulation $simulation): string
    {
        $baseName = trim((string) $simulation->name);

        if ($baseName === '') {
            return 'Simulacao duplicada';
        }

        if (! str_contains(mb_strtolower($baseName), 'copia')) {
            return $baseName . ' - copia';
        }

        return $baseName . ' 2';
    }
}
