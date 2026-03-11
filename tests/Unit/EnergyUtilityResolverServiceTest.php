<?php

namespace Tests\Unit;

use App\Modules\Solar\Models\EnergyUtility;
use App\Modules\Solar\Services\EnergyUtilityResolverService;
use Tests\TestCase;

class EnergyUtilityResolverServiceTest extends TestCase
{
    public function test_it_rejects_utility_when_state_does_not_match(): void
    {
        $service = new EnergyUtilityResolverService();
        $utility = new EnergyUtility([
            'name' => 'Enel RJ',
            'state' => 'RJ',
            'cities_json' => ['Rio de Janeiro'],
        ]);

        $this->assertFalse($service->matchesLocation($utility, 'Caieiras', 'SP'));
    }

    public function test_it_accepts_utility_when_state_and_city_match(): void
    {
        $service = new EnergyUtilityResolverService();
        $utility = new EnergyUtility([
            'name' => 'Enel SP',
            'state' => 'SP',
            'cities_json' => ['Caieiras', 'Sao Paulo'],
        ]);

        $this->assertTrue($service->matchesLocation($utility, 'Caieiras', 'SP'));
    }

    public function test_it_accepts_utility_when_city_is_missing_but_state_matches(): void
    {
        $service = new EnergyUtilityResolverService();
        $utility = new EnergyUtility([
            'name' => 'Concessionaria SP',
            'state' => 'SP',
            'cities_json' => ['Caieiras'],
        ]);

        $this->assertTrue($service->matchesLocation($utility, null, 'SP'));
    }
}
