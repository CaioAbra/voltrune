<?php

namespace App\Modules\Solar\Services;

use App\Modules\Solar\Models\SolarCompanySetting;

class SolarSizingService
{
    public function estimateRequiredPowerKwp(float|int|string|null $monthlyConsumptionKwh): ?float
    {
        if ($monthlyConsumptionKwh === null || $monthlyConsumptionKwh === '') {
            return null;
        }

        $consumption = (float) $monthlyConsumptionKwh;

        if ($consumption <= 0) {
            return null;
        }

        return round($consumption / 130, 2);
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

    public function estimateGenerationKwh(float|int|string|null $systemPowerKwp): ?float
    {
        $powerKwp = $systemPowerKwp !== null && $systemPowerKwp !== '' ? (float) $systemPowerKwp : 0.0;

        if ($powerKwp <= 0) {
            return null;
        }

        return round($powerKwp * 130, 2);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function applySuggestedSizing(array $data, ?SolarCompanySetting $setting = null): array
    {
        $defaultModulePower = $setting?->default_module_power ?: 550;

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
            $data['system_power_kwp'] = $this->estimateRequiredPowerKwp($data['monthly_consumption_kwh'] ?? null);
        }

        if (! isset($data['module_quantity']) || $data['module_quantity'] === null || $data['module_quantity'] === '') {
            $data['module_quantity'] = $this->estimateModuleQuantity($data['system_power_kwp'] ?? null, $data['module_power'] ?? null);
        }

        if (! isset($data['estimated_generation_kwh']) || $data['estimated_generation_kwh'] === null || $data['estimated_generation_kwh'] === '') {
            $data['estimated_generation_kwh'] = $this->estimateGenerationKwh($data['system_power_kwp'] ?? null);
        }

        return $data;
    }
}
