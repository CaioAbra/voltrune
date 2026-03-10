<?php

namespace App\Modules\Solar\Services;

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

        return round($consumption / 120, 2);
    }
}
