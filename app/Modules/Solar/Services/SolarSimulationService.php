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
                'name' => 'Leitura inicial',
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
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    public function rebuildPayload(
        SolarSimulation $simulation,
        SolarProject $project,
        ?SolarCompanySetting $companySetting = null,
        array $overrides = [],
    ): array {
        $payload = array_merge(
            $simulation->only([
                'system_power_kwp',
                'module_power',
                'module_quantity',
                'estimated_generation_kwh',
                'inverter_model',
                'solar_factor_used',
                'solar_factor_source',
                'suggested_price',
                'payment_mode',
                'upfront_payment',
                'installment_count',
                'monthly_interest_rate',
                'tariff_growth_yearly',
                'status',
                'notes',
            ]),
            $overrides,
        );

        $solarFactor = $payload['solar_factor_used'] ?? $simulation->solar_factor_used ?? $project->solar_factor_used;
        $systemPower = $payload['system_power_kwp'] ?? $simulation->system_power_kwp;
        $modulePower = $payload['module_power'] ?? $simulation->module_power ?? $companySetting?->default_module_power;
        $moduleQuantity = $payload['module_quantity'] ?? $simulation->module_quantity;
        $generation = $payload['estimated_generation_kwh'] ?? $simulation->estimated_generation_kwh;
        $price = $payload['suggested_price'] ?? $simulation->suggested_price;
        $inverterModel = $payload['inverter_model'] ?? $simulation->inverter_model ?? $companySetting?->default_inverter_model;
        $paymentMode = ($payload['payment_mode'] ?? $simulation->payment_mode ?? 'cash') === 'financed' ? 'financed' : 'cash';
        $upfrontPayment = $payload['upfront_payment'] ?? $simulation->upfront_payment;
        $installmentCount = $payload['installment_count']
            ?? $simulation->installment_count
            ?? ($paymentMode === 'financed' ? SolarSizingService::DEFAULT_INSTALLMENT_COUNT : null);
        $monthlyInterestRate = $payload['monthly_interest_rate']
            ?? $simulation->monthly_interest_rate
            ?? SolarSizingService::DEFAULT_MONTHLY_INTEREST_RATE;
        $tariffGrowthYearly = $payload['tariff_growth_yearly']
            ?? $simulation->tariff_growth_yearly
            ?? SolarSizingService::DEFAULT_TARIFF_GROWTH_YEARLY;

        if (($systemPower === null || $systemPower === '') && $project->monthly_consumption_kwh !== null) {
            $systemPower = $this->sizing->estimateRequiredPowerKwp($project->monthly_consumption_kwh, $solarFactor);
        }

        if (($moduleQuantity === null || $moduleQuantity === '') && $systemPower !== null) {
            $moduleQuantity = $this->sizing->estimateModuleQuantity($systemPower, $modulePower);
        }

        if (($generation === null || $generation === '') && $systemPower !== null) {
            $generation = $this->sizing->estimateGenerationKwh($systemPower, $solarFactor);
        }

        if (($price === null || $price === '') && $systemPower !== null) {
            $priceContext = $this->sizing->resolveContextualPricePerKwp($companySetting?->price_per_kwp, $project->state);
            $price = $this->sizing->estimateSuggestedPrice($systemPower, $priceContext['value']);
        }

        $marginContext = $this->sizing->resolveMarginContext($companySetting, $systemPower);
        $kitCost = $this->sizing->estimateKitCostForMarginContext($price, $marginContext);
        $kitBreakdown = $this->sizing->estimateKitCostBreakdown($kitCost);
        $monthlySavings = $this->sizing->estimateMonthlySavings($project->energy_bill_value);
        $financedAmount = $paymentMode === 'financed'
            ? $this->sizing->estimateFinancedAmount($price, $upfrontPayment)
            : null;
        $installmentValue = $paymentMode === 'financed'
            ? $this->sizing->estimateInstallmentValue($financedAmount, $installmentCount, $monthlyInterestRate)
            : null;
        $netMonthlyBenefit = $paymentMode === 'financed'
            ? $this->sizing->estimateNetMonthlyBenefit($monthlySavings, $installmentValue)
            : $monthlySavings;
        $projectedFiveYearSavings = $this->sizing->estimateProjectedSavingsWithTariffGrowth(
            $monthlySavings,
            $tariffGrowthYearly,
            5,
        );

        return [
            'system_power_kwp' => $systemPower,
            'module_power' => $modulePower,
            'module_quantity' => $moduleQuantity,
            'estimated_generation_kwh' => $generation,
            'area_estimated' => $this->sizing->estimateAreaFromModules($moduleQuantity)
                ?: $this->sizing->estimateAreaSquareMeters($systemPower),
            'inverter_model' => $inverterModel,
            'solar_factor_used' => $solarFactor,
            'solar_factor_source' => $payload['solar_factor_source'] ?? $simulation->solar_factor_source ?? $project->solar_factor_source,
            'suggested_price' => $price,
            'payment_mode' => $paymentMode,
            'upfront_payment' => $upfrontPayment,
            'installment_count' => $paymentMode === 'financed' ? $installmentCount : null,
            'monthly_interest_rate' => $monthlyInterestRate,
            'tariff_growth_yearly' => $tariffGrowthYearly,
            'estimated_module_cost' => $kitBreakdown['modules'],
            'estimated_inverter_cost' => $kitBreakdown['inverter'],
            'estimated_structure_cost' => $kitBreakdown['structure'],
            'estimated_installation_cost' => $kitBreakdown['installation'],
            'estimated_kit_cost' => $kitCost,
            'estimated_gross_profit' => $this->sizing->estimateGrossProfit($price, $kitCost),
            'estimated_monthly_savings' => $monthlySavings,
            'estimated_annual_savings' => $this->sizing->estimateAnnualSavings($project->energy_bill_value),
            'estimated_lifetime_savings' => $this->sizing->estimateLifetimeSavings($project->energy_bill_value),
            'estimated_roi' => $this->sizing->estimateRoiPercentage($price, $project->energy_bill_value),
            'estimated_payback_months' => $this->sizing->estimatePaybackMonths($price, $project->energy_bill_value),
            'estimated_financed_amount' => $financedAmount,
            'estimated_installment_value' => $installmentValue,
            'estimated_net_monthly_benefit' => $netMonthlyBenefit,
            'estimated_five_year_savings' => $projectedFiveYearSavings,
            'system_composition_json' => $this->sizing->resolveSystemComposition(
                $moduleQuantity,
                $modulePower,
                $inverterModel,
                $systemPower,
            ),
            'status' => $payload['status'] ?? $simulation->status ?? 'draft',
            'notes' => $payload['notes'] ?? $simulation->notes,
        ];
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
        $paymentMode = 'cash';
        $tariffGrowthYearly = SolarSizingService::DEFAULT_TARIFF_GROWTH_YEARLY;
        $areaEstimated = $this->sizing->estimateAreaFromModules($moduleQuantity)
            ?: $this->sizing->estimateAreaSquareMeters($systemPower);
        $marginContext = $this->sizing->resolveMarginContext($companySetting, $systemPower);
        $kitCost = $this->sizing->estimateKitCostForMarginContext($price, $marginContext);
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
            'payment_mode' => $paymentMode,
            'upfront_payment' => null,
            'installment_count' => null,
            'monthly_interest_rate' => SolarSizingService::DEFAULT_MONTHLY_INTEREST_RATE,
            'tariff_growth_yearly' => $tariffGrowthYearly,
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
            'estimated_financed_amount' => null,
            'estimated_installment_value' => null,
            'estimated_net_monthly_benefit' => $monthlySavings,
            'estimated_five_year_savings' => $this->sizing->estimateProjectedSavingsWithTariffGrowth($monthlySavings, $tariffGrowthYearly, 5),
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

        if ($nextPosition === 1) {
            return 'Leitura inicial';
        }

        return 'Revisao ' . str_pad((string) ($nextPosition - 1), 2, '0', STR_PAD_LEFT);
    }

    private function resolveDuplicateName(SolarSimulation $simulation): string
    {
        $baseName = trim((string) $simulation->name);

        if ($baseName === '') {
            return 'Simulacao variante';
        }

        if (preg_match('/^(.*?)(?: - variante(?: (\d+))?)$/i', $baseName, $matches) === 1) {
            $prefix = trim((string) ($matches[1] ?? $baseName));
            $currentVersion = isset($matches[2]) ? (int) $matches[2] : 1;
            $nextVersion = $currentVersion + 1;

            return $prefix . ' - variante ' . $nextVersion;
        }

        return $baseName . ' - variante';
    }
}
