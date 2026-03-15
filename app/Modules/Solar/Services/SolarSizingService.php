<?php

namespace App\Modules\Solar\Services;

use App\Modules\Solar\Models\SolarCompanyMarginRange;
use App\Modules\Solar\Models\SolarCompanySetting;
use App\Modules\Solar\Models\SolarMarketDefault;
use Throwable;

class SolarSizingService
{
    public const MARKET_PRICE_PER_KWP = 4200.0;
    public const MINIMUM_RESIDUAL_ENERGY_COST = 70.0;
    public const DEFAULT_SOLAR_FACTOR = 130.0;
    public const ESTIMATED_AREA_PER_KWP = 4.5;
    public const MODULE_AREA_SQM = 2.3;
    public const AVERAGE_DAYS_PER_MONTH = 30.0;
    public const DEFAULT_TARIFF_GROWTH_YEARLY = 6.0;
    public const DEFAULT_INSTALLMENT_COUNT = 60;
    public const DEFAULT_MONTHLY_INTEREST_RATE = 1.25;

    private const DEFAULT_REGIONAL_PRICE_PER_KWP = [
        'AC' => 4550.0,
        'AL' => 4380.0,
        'AM' => 4600.0,
        'AP' => 4580.0,
        'BA' => 4350.0,
        'CE' => 4340.0,
        'DF' => 4250.0,
        'ES' => 4280.0,
        'GO' => 4240.0,
        'MA' => 4420.0,
        'MG' => 4300.0,
        'MS' => 4230.0,
        'MT' => 4260.0,
        'PA' => 4520.0,
        'PB' => 4360.0,
        'PE' => 4370.0,
        'PI' => 4410.0,
        'PR' => 4180.0,
        'RJ' => 4320.0,
        'RN' => 4350.0,
        'RO' => 4480.0,
        'RR' => 4620.0,
        'RS' => 4170.0,
        'SC' => 4190.0,
        'SE' => 4370.0,
        'SP' => 4310.0,
        'TO' => 4440.0,
    ];
    public const DEFAULT_GROSS_MARGIN_PERCENT = 22.0;
    private const MARKET_COMPONENT_SHARE = [
        'modules' => 0.50,
        'inverter' => 0.20,
        'installation' => 0.18,
    ];
    private const COMPONENT_COST_SHARE = [
        'modules' => 0.50,
        'inverter' => 0.20,
        'structure' => 0.12,
        'installation' => 0.18,
    ];
    private const EQUIPMENT_BLUEPRINT = [
        'modules' => [
            'label' => 'Modulos fotovoltaicos',
            'category' => 'module',
            'default_description' => 'Quantidade e potencia definidas automaticamente',
        ],
        'inverter' => [
            'label' => 'Inversor',
            'category' => 'inverter',
            'default_description' => 'Inversor compativel com o sistema sugerido',
        ],
        'structure' => [
            'label' => 'Estrutura',
            'category' => 'structure',
            'default_description' => 'Estrutura de fixacao compativel com o local da instalacao',
        ],
        'installation' => [
            'label' => 'Itens basicos de instalacao',
            'category' => 'installation',
            'default_description' => 'Cabos, conectores, protecoes eletricas e mao de obra basica',
        ],
    ];

    public function resolveSolarFactor(float|int|string|null $solarFactor): float
    {
        $factor = $solarFactor !== null && $solarFactor !== '' ? (float) $solarFactor : 0.0;

        if ($factor > 0) {
            return round($factor, 2);
        }

        return self::DEFAULT_SOLAR_FACTOR;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function resolveProjectSolarFactor(array $data, float|int|string|null $fallbackFactor = null): float
    {
        return $this->resolveSolarFactor($data['solar_factor_used'] ?? $fallbackFactor);
    }

    public function resolvePricePerKwp(float|int|string|null $pricePerKwp): float
    {
        $price = $pricePerKwp !== null && $pricePerKwp !== '' ? (float) $pricePerKwp : 0.0;

        if ($price > 0) {
            return round($price, 2);
        }

        return self::MARKET_PRICE_PER_KWP;
    }

    public function resolveRegionalPricePerKwp(?string $state): ?float
    {
        $marketDefaults = $this->resolveMarketDefaults($state);

        if ($marketDefaults['source'] !== 'regional') {
            return null;
        }

        return $marketDefaults['price_per_kwp'];
    }

    /**
     * @return array{
     *     state: string,
     *     price_per_kwp: float,
     *     module_cost_average: float,
     *     inverter_cost_average: float,
     *     installation_cost_average: float,
     *     source: string
     * }
     */
    public function resolveMarketDefaults(?string $state = null): array
    {
        $normalizedState = strtoupper(trim((string) $state));

        if ($marketDefault = $this->findMarketDefault($normalizedState)) {
            $resolvedState = strtoupper((string) $marketDefault->state);

            return [
                'state' => $resolvedState,
                'price_per_kwp' => round((float) $marketDefault->price_per_kwp, 2),
                'module_cost_average' => round((float) $marketDefault->module_cost_average, 2),
                'inverter_cost_average' => round((float) $marketDefault->inverter_cost_average, 2),
                'installation_cost_average' => round((float) $marketDefault->installation_cost_average, 2),
                'source' => $normalizedState !== '' && $resolvedState === $normalizedState ? 'regional' : 'fallback',
            ];
        }

        $fallbackRegionalPrice = self::DEFAULT_REGIONAL_PRICE_PER_KWP[$normalizedState] ?? null;

        if ($fallbackRegionalPrice !== null) {
            return $this->buildFallbackMarketDefaults($normalizedState, $fallbackRegionalPrice, 'regional');
        }

        return $this->buildFallbackMarketDefaults('BR', self::MARKET_PRICE_PER_KWP, 'fallback');
    }

    /**
     * @return array{
     *     value: float,
     *     source: string,
     *     market_defaults: array{
     *         state: string,
     *         price_per_kwp: float,
     *         module_cost_average: float,
     *         inverter_cost_average: float,
     *         installation_cost_average: float,
     *         source: string
     *     }
     * }
     */
    public function resolveContextualPricePerKwp(float|int|string|null $companyPricePerKwp, ?string $state = null): array
    {
        $companyPrice = $companyPricePerKwp !== null && $companyPricePerKwp !== '' ? (float) $companyPricePerKwp : 0.0;
        $marketDefaults = $this->resolveMarketDefaults($state);

        if ($companyPrice > 0) {
            return [
                'value' => round($companyPrice, 2),
                'source' => 'company',
                'market_defaults' => $marketDefaults,
            ];
        }

        return [
            'value' => $marketDefaults['price_per_kwp'],
            'source' => $marketDefaults['source'],
            'market_defaults' => $marketDefaults,
        ];
    }

    /**
     * @return array<string, float>
     */
    public function regionalPriceLookup(): array
    {
        try {
            $lookup = SolarMarketDefault::query()
                ->where('state', '!=', 'BR')
                ->orderBy('state')
                ->get(['state', 'price_per_kwp'])
                ->mapWithKeys(fn (SolarMarketDefault $marketDefault): array => [
                    strtoupper((string) $marketDefault->state) => round((float) $marketDefault->price_per_kwp, 2),
                ])
                ->all();

            if ($lookup !== []) {
                return $lookup;
            }
        } catch (Throwable) {
            // Fallback para ambientes ainda sem a tabela de referencia criada.
        }

        return self::DEFAULT_REGIONAL_PRICE_PER_KWP;
    }

    /**
     * Estrutura leve para evoluir depois para um catalogo real por empresa/SKU.
     *
     * @return array<string, array{label: string, category: string, default_description: string}>
     */
    public function equipmentBlueprint(): array
    {
        return self::EQUIPMENT_BLUEPRINT;
    }

    public function estimateRequiredPowerKwp(float|int|string|null $monthlyConsumptionKwh, float|int|string|null $solarFactor = null): ?float
    {
        if ($monthlyConsumptionKwh === null || $monthlyConsumptionKwh === '') {
            return null;
        }

        $consumption = (float) $monthlyConsumptionKwh;
        $factor = $this->resolveSolarFactor($solarFactor);

        if ($consumption <= 0 || $factor <= 0) {
            return null;
        }

        return round($consumption / $factor, 2);
    }

    public function estimateModuleQuantity(float|int|string|null $systemPowerKwp, float|int|string|null $modulePower): ?int
    {
        $powerKwp = $systemPowerKwp !== null && $systemPowerKwp !== '' ? (float) $systemPowerKwp : 0.0;
        $modulePowerWp = $modulePower !== null && $modulePower !== '' ? (int) $modulePower : 0;

        if ($powerKwp <= 0 || $modulePowerWp <= 0) {
            return null;
        }

        return (int) ceil(($powerKwp * 1000) / $modulePowerWp);
    }

    public function estimateGenerationKwh(float|int|string|null $systemPowerKwp, float|int|string|null $solarFactor = null): ?float
    {
        $powerKwp = $systemPowerKwp !== null && $systemPowerKwp !== '' ? (float) $systemPowerKwp : 0.0;
        $factor = $this->resolveSolarFactor($solarFactor);

        if ($powerKwp <= 0 || $factor <= 0) {
            return null;
        }

        return round($powerKwp * $factor, 2);
    }

    public function estimateEquivalentSolarRadiationDaily(float|int|string|null $solarFactor): float
    {
        $factor = $this->resolveSolarFactor($solarFactor);

        return round($factor / self::AVERAGE_DAYS_PER_MONTH, 2);
    }

    public function estimateSuggestedPrice(
        float|int|string|null $systemPowerKwp,
        float|int|string|null $pricePerKwp,
    ): ?float {
        $powerKwp = $systemPowerKwp !== null && $systemPowerKwp !== '' ? (float) $systemPowerKwp : 0.0;
        $price = $this->resolvePricePerKwp($pricePerKwp);

        if ($powerKwp <= 0) {
            return null;
        }

        return round($powerKwp * $price, 2);
    }

    /**
     * @return array{
     *     mode: string,
     *     source: string,
     *     power_kwp: ?float,
     *     margin_percent: ?float,
     *     requires_negotiation: bool,
     *     has_match: bool,
     *     uses_default_margin: bool,
     *     range: ?array{
     *         id: ?int,
     *         min_kwp: float,
     *         max_kwp: ?float,
     *         margin_percent: ?float,
     *         requires_negotiation: bool
     *     }
     * }
     */
    public function resolveMarginContext(
        ?SolarCompanySetting $setting,
        float|int|string|null $systemPowerKwp,
    ): array {
        $power = $systemPowerKwp !== null && $systemPowerKwp !== '' ? round((float) $systemPowerKwp, 2) : null;
        $mode = $setting?->margin_mode === SolarCompanySetting::MARGIN_MODE_RANGE
            ? SolarCompanySetting::MARGIN_MODE_RANGE
            : SolarCompanySetting::MARGIN_MODE_FIXED;

        if ($mode === SolarCompanySetting::MARGIN_MODE_RANGE) {
            if ($power === null || $power <= 0) {
                return [
                    'mode' => $mode,
                    'source' => 'pending',
                    'power_kwp' => $power,
                    'margin_percent' => null,
                    'requires_negotiation' => false,
                    'has_match' => false,
                    'uses_default_margin' => false,
                    'range' => null,
                ];
            }

            $ranges = $this->resolveMarginRanges($setting);

            foreach ($ranges as $range) {
                $minKwp = round((float) $range->min_kwp, 2);
                $maxKwp = $range->max_kwp !== null ? round((float) $range->max_kwp, 2) : null;

                if ($power < $minKwp) {
                    continue;
                }

                if ($maxKwp !== null && $power > $maxKwp) {
                    continue;
                }

                return [
                    'mode' => $mode,
                    'source' => 'range',
                    'power_kwp' => $power,
                    'margin_percent' => $range->margin_percent !== null ? round((float) $range->margin_percent, 2) : null,
                    'requires_negotiation' => (bool) $range->requires_negotiation,
                    'has_match' => true,
                    'uses_default_margin' => false,
                    'range' => [
                        'id' => $range->getKey(),
                        'min_kwp' => $minKwp,
                        'max_kwp' => $maxKwp,
                        'margin_percent' => $range->margin_percent !== null ? round((float) $range->margin_percent, 2) : null,
                        'requires_negotiation' => (bool) $range->requires_negotiation,
                    ],
                ];
            }

            return [
                'mode' => $mode,
                'source' => 'unmatched',
                'power_kwp' => $power,
                'margin_percent' => null,
                'requires_negotiation' => false,
                'has_match' => false,
                'uses_default_margin' => false,
                'range' => null,
            ];
        }

        $marginPercent = $setting?->margin_percent !== null ? round((float) $setting->margin_percent, 2) : null;

        return [
            'mode' => $mode,
            'source' => $marginPercent !== null ? 'fixed' : 'default',
            'power_kwp' => $power,
            'margin_percent' => $marginPercent,
            'requires_negotiation' => false,
            'has_match' => true,
            'uses_default_margin' => $marginPercent === null,
            'range' => null,
        ];
    }

    public function estimateMonthlySavings(float|int|string|null $energyBillValue): ?float
    {
        if ($energyBillValue === null || $energyBillValue === '') {
            return null;
        }

        $billValue = (float) $energyBillValue;

        if ($billValue <= 0) {
            return null;
        }

        return round(max($billValue - self::MINIMUM_RESIDUAL_ENERGY_COST, 0), 2);
    }

    public function estimateAnnualSavings(float|int|string|null $energyBillValue): ?float
    {
        $monthlySavings = $this->estimateMonthlySavings($energyBillValue);

        if ($monthlySavings === null) {
            return null;
        }

        return round($monthlySavings * 12, 2);
    }

    public function estimateLifetimeSavings(float|int|string|null $energyBillValue, int $years = 25): ?float
    {
        $annualSavings = $this->estimateAnnualSavings($energyBillValue);

        if ($annualSavings === null || $years <= 0) {
            return null;
        }

        return round($annualSavings * $years, 2);
    }

    public function estimateProjectedSavingsWithTariffGrowth(
        float|int|string|null $monthlySavings,
        float|int|string|null $tariffGrowthYearly = null,
        int $years = 5,
    ): ?float {
        $baseSavings = $monthlySavings !== null && $monthlySavings !== '' ? (float) $monthlySavings : 0.0;
        $growthRate = $tariffGrowthYearly !== null && $tariffGrowthYearly !== ''
            ? (float) $tariffGrowthYearly
            : self::DEFAULT_TARIFF_GROWTH_YEARLY;

        if ($baseSavings <= 0 || $years <= 0) {
            return null;
        }

        $projectedSavings = 0.0;

        for ($year = 0; $year < $years; $year++) {
            $projectedSavings += ($baseSavings * 12) * ((1 + ($growthRate / 100)) ** $year);
        }

        return round($projectedSavings, 2);
    }

    public function estimateFinancedAmount(
        float|int|string|null $suggestedPrice,
        float|int|string|null $upfrontPayment = null,
    ): ?float {
        $price = $suggestedPrice !== null && $suggestedPrice !== '' ? (float) $suggestedPrice : 0.0;
        $entry = $upfrontPayment !== null && $upfrontPayment !== '' ? (float) $upfrontPayment : 0.0;

        if ($price <= 0) {
            return null;
        }

        return round(max($price - max($entry, 0), 0), 2);
    }

    public function estimateInstallmentValue(
        float|int|string|null $financedAmount,
        int|float|string|null $installmentCount,
        float|int|string|null $monthlyInterestRate = null,
    ): ?float {
        $principal = $financedAmount !== null && $financedAmount !== '' ? (float) $financedAmount : 0.0;
        $months = $installmentCount !== null && $installmentCount !== '' ? (int) $installmentCount : 0;
        $ratePercent = $monthlyInterestRate !== null && $monthlyInterestRate !== ''
            ? (float) $monthlyInterestRate
            : self::DEFAULT_MONTHLY_INTEREST_RATE;

        if ($principal <= 0 || $months <= 0) {
            return null;
        }

        $rate = $ratePercent / 100;

        if ($rate <= 0) {
            return round($principal / $months, 2);
        }

        $factor = ((1 + $rate) ** $months);

        return round($principal * (($rate * $factor) / ($factor - 1)), 2);
    }

    public function estimateNetMonthlyBenefit(
        float|int|string|null $monthlySavings,
        float|int|string|null $installmentValue = null,
    ): ?float {
        $savings = $monthlySavings !== null && $monthlySavings !== '' ? (float) $monthlySavings : 0.0;
        $installment = $installmentValue !== null && $installmentValue !== '' ? (float) $installmentValue : 0.0;

        if ($savings <= 0 && $installment <= 0) {
            return null;
        }

        return round($savings - $installment, 2);
    }

    public function estimateAreaSquareMeters(float|int|string|null $systemPowerKwp): ?float
    {
        $powerKwp = $systemPowerKwp !== null && $systemPowerKwp !== '' ? (float) $systemPowerKwp : 0.0;

        if ($powerKwp <= 0) {
            return null;
        }

        return round($powerKwp * self::ESTIMATED_AREA_PER_KWP, 2);
    }

    public function estimateAreaFromModules(int|float|string|null $moduleQuantity): ?float
    {
        $modules = $moduleQuantity !== null && $moduleQuantity !== '' ? (int) $moduleQuantity : 0;

        if ($modules <= 0) {
            return null;
        }

        return round($modules * self::MODULE_AREA_SQM, 2);
    }

    /**
     * @return array<int, array{label: string, detail: string}>
     */
    public function resolveSystemComposition(
        int|float|string|null $moduleQuantity,
        int|float|string|null $modulePower,
        ?string $inverterModel,
        float|int|string|null $systemPowerKwp,
    ): array {
        $modules = $moduleQuantity !== null && $moduleQuantity !== '' ? (int) $moduleQuantity : 0;
        $moduleWp = $modulePower !== null && $modulePower !== '' ? (int) $modulePower : 0;
        $resolvedInverterModel = trim((string) $inverterModel);
        $systemPower = $systemPowerKwp !== null && $systemPowerKwp !== '' ? (float) $systemPowerKwp : 0.0;

        $blueprint = $this->equipmentBlueprint();

        return [
            [
                'label' => $blueprint['modules']['label'],
                'detail' => $modules > 0 && $moduleWp > 0
                    ? sprintf('%d modulos de %d W', $modules, $moduleWp)
                    : $blueprint['modules']['default_description'],
            ],
            [
                'label' => $blueprint['inverter']['label'],
                'detail' => $resolvedInverterModel !== ''
                    ? $resolvedInverterModel
                    : ($systemPower > 0 ? sprintf('Inversor compativel com %.2f kWp', $systemPower) : $blueprint['inverter']['default_description']),
            ],
            [
                'label' => $blueprint['structure']['label'],
                'detail' => $blueprint['structure']['default_description'],
            ],
            [
                'label' => $blueprint['installation']['label'],
                'detail' => $blueprint['installation']['default_description'],
            ],
        ];
    }

    public function estimateKitCost(
        float|int|string|null $suggestedPrice,
        float|int|string|null $marginPercent = null,
    ): ?float {
        $price = $suggestedPrice !== null && $suggestedPrice !== '' ? (float) $suggestedPrice : 0.0;
        $margin = $marginPercent !== null && $marginPercent !== '' ? (float) $marginPercent : self::DEFAULT_GROSS_MARGIN_PERCENT;

        if ($price <= 0) {
            return null;
        }

        if ($margin > 0) {
            return round($price / (1 + ($margin / 100)), 2);
        }

        return round($price * (1 - (self::DEFAULT_GROSS_MARGIN_PERCENT / 100)), 2);
    }

    /**
     * @param array{
     *     mode?: string,
     *     margin_percent?: ?float,
     *     requires_negotiation?: bool,
     *     has_match?: bool
     * } $marginContext
     */
    public function estimateKitCostForMarginContext(
        float|int|string|null $suggestedPrice,
        array $marginContext,
    ): ?float {
        if (($marginContext['mode'] ?? SolarCompanySetting::MARGIN_MODE_FIXED) === SolarCompanySetting::MARGIN_MODE_RANGE) {
            if (($marginContext['requires_negotiation'] ?? false) || ! ($marginContext['has_match'] ?? false)) {
                return null;
            }
        }

        return $this->estimateKitCost($suggestedPrice, $marginContext['margin_percent'] ?? null);
    }

    /**
     * @return array{modules: ?float, inverter: ?float, structure: ?float, installation: ?float}
     */
    public function estimateKitCostBreakdown(float|int|string|null $kitCost): array
    {
        $cost = $kitCost !== null && $kitCost !== '' ? (float) $kitCost : 0.0;

        if ($cost <= 0) {
            return [
                'modules' => null,
                'inverter' => null,
                'structure' => null,
                'installation' => null,
            ];
        }

        return [
            'modules' => round($cost * self::COMPONENT_COST_SHARE['modules'], 2),
            'inverter' => round($cost * self::COMPONENT_COST_SHARE['inverter'], 2),
            'structure' => round($cost * self::COMPONENT_COST_SHARE['structure'], 2),
            'installation' => round($cost * self::COMPONENT_COST_SHARE['installation'], 2),
        ];
    }

    public function estimateGrossProfit(
        float|int|string|null $suggestedPrice,
        float|int|string|null $kitCost,
    ): ?float {
        $price = $suggestedPrice !== null && $suggestedPrice !== '' ? (float) $suggestedPrice : 0.0;
        $cost = $kitCost !== null && $kitCost !== '' ? (float) $kitCost : 0.0;

        if ($price <= 0 || $cost <= 0) {
            return null;
        }

        return round($price - $cost, 2);
    }

    /**
     * @return list<SolarCompanyMarginRange>
     */
    private function resolveMarginRanges(?SolarCompanySetting $setting): array
    {
        if (! $setting instanceof SolarCompanySetting) {
            return [];
        }

        if ($setting->relationLoaded('marginRanges')) {
            /** @var list<SolarCompanyMarginRange> $ranges */
            $ranges = $setting->marginRanges->all();

            return $ranges;
        }

        /** @var list<SolarCompanyMarginRange> $ranges */
        $ranges = $setting->marginRanges()->get()->all();

        return $ranges;
    }

    public function estimatePaybackMonths(
        float|int|string|null $suggestedPrice,
        float|int|string|null $energyBillValue,
    ): ?int {
        $price = $suggestedPrice !== null && $suggestedPrice !== '' ? (float) $suggestedPrice : 0.0;
        $annualSavings = $this->estimateAnnualSavings($energyBillValue);

        if ($price <= 0 || $annualSavings === null || $annualSavings <= 0) {
            return null;
        }

        return (int) ceil(($price / $annualSavings) * 12);
    }

    public function estimatePaybackYears(
        float|int|string|null $suggestedPrice,
        float|int|string|null $energyBillValue,
    ): ?float {
        $price = $suggestedPrice !== null && $suggestedPrice !== '' ? (float) $suggestedPrice : 0.0;
        $annualSavings = $this->estimateAnnualSavings($energyBillValue);

        if ($price <= 0 || $annualSavings === null || $annualSavings <= 0) {
            return null;
        }

        return round($price / $annualSavings, 1);
    }

    public function estimateRoiPercentage(
        float|int|string|null $suggestedPrice,
        float|int|string|null $energyBillValue,
    ): ?float {
        $price = $suggestedPrice !== null && $suggestedPrice !== '' ? (float) $suggestedPrice : 0.0;
        $annualSavings = $this->estimateAnnualSavings($energyBillValue);

        if ($price <= 0 || $annualSavings === null || $annualSavings <= 0) {
            return null;
        }

        return round(($annualSavings / $price) * 100, 1);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function applySuggestedSizing(
        array $data,
        ?SolarCompanySetting $setting = null,
        float|int|string|null $solarFactor = null,
        float|int|string|null $pricePerKwp = null,
    ): array {
        $defaultModulePower = $setting?->default_module_power ?: 550;
        $resolvedSolarFactor = $this->resolveProjectSolarFactor($data, $solarFactor);
        $resolvedPricePerKwp = $pricePerKwp !== null && $pricePerKwp !== ''
            ? $this->resolvePricePerKwp($pricePerKwp)
            : $this->resolveContextualPricePerKwp($setting?->price_per_kwp, $data['state'] ?? null)['value'];

        $data['solar_factor_used'] = $resolvedSolarFactor;

        $modulePower = isset($data['module_power']) && $data['module_power'] !== ''
            ? (int) $data['module_power']
            : $defaultModulePower;

        if (! isset($data['module_power']) || $data['module_power'] === null || $data['module_power'] === '') {
            $data['module_power'] = $modulePower;
        }

        if (
            $setting?->default_inverter_model
            && (! isset($data['inverter_model']) || $data['inverter_model'] === null || trim((string) $data['inverter_model']) === '')
        ) {
            $data['inverter_model'] = $setting->default_inverter_model;
        }

        if (! isset($data['system_power_kwp']) || $data['system_power_kwp'] === null || $data['system_power_kwp'] === '') {
            $data['system_power_kwp'] = $this->estimateRequiredPowerKwp($data['monthly_consumption_kwh'] ?? null, $resolvedSolarFactor);
        }

        if (! isset($data['module_quantity']) || $data['module_quantity'] === null || $data['module_quantity'] === '') {
            $data['module_quantity'] = $this->estimateModuleQuantity($data['system_power_kwp'] ?? null, $data['module_power'] ?? null);
        }

        if (! isset($data['estimated_generation_kwh']) || $data['estimated_generation_kwh'] === null || $data['estimated_generation_kwh'] === '') {
            $data['estimated_generation_kwh'] = $this->estimateGenerationKwh($data['system_power_kwp'] ?? null, $resolvedSolarFactor);
        }

        if (! isset($data['suggested_price']) || $data['suggested_price'] === null || $data['suggested_price'] === '') {
            $data['suggested_price'] = $this->estimateSuggestedPrice(
                $data['system_power_kwp'] ?? null,
                $resolvedPricePerKwp,
            );
        }

        return $data;
    }

    private function findMarketDefault(string $normalizedState): ?SolarMarketDefault
    {
        try {
            if ($normalizedState !== '') {
                $regional = SolarMarketDefault::query()
                    ->where('state', $normalizedState)
                    ->first();

                if ($regional instanceof SolarMarketDefault) {
                    return $regional;
                }
            }

            $fallback = SolarMarketDefault::query()
                ->where('state', 'BR')
                ->first();

            return $fallback instanceof SolarMarketDefault ? $fallback : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array{
     *     state: string,
     *     price_per_kwp: float,
     *     module_cost_average: float,
     *     inverter_cost_average: float,
     *     installation_cost_average: float,
     *     source: string
     * }
     */
    private function buildFallbackMarketDefaults(string $state, float $pricePerKwp, string $source): array
    {
        $price = round($pricePerKwp, 2);

        return [
            'state' => $state,
            'price_per_kwp' => $price,
            'module_cost_average' => $this->estimateMarketComponentAverage($price, 'modules'),
            'inverter_cost_average' => $this->estimateMarketComponentAverage($price, 'inverter'),
            'installation_cost_average' => $this->estimateMarketComponentAverage($price, 'installation'),
            'source' => $source,
        ];
    }

    private function estimateMarketComponentAverage(float $pricePerKwp, string $component): float
    {
        $share = self::MARKET_COMPONENT_SHARE[$component] ?? 0;

        return round($pricePerKwp * $share, 2);
    }
}
