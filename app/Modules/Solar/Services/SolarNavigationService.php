<?php

namespace App\Modules\Solar\Services;

class SolarNavigationService
{
    /**
     * @return array<int, array{label: string, route: string, active: string}>
     */
    public function items(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'route' => 'solar.dashboard',
                'active' => 'solar.dashboard',
            ],
            [
                'label' => 'Clientes',
                'route' => 'solar.customers.index',
                'active' => 'solar.customers.*',
            ],
            [
                'label' => 'Simulacoes',
                'route' => 'solar.simulations.index',
                'active' => 'solar.simulations.*',
            ],
            [
                'label' => 'Orcamentos',
                'route' => 'solar.quotes.index',
                'active' => 'solar.quotes.*',
            ],
        ];
    }
}
