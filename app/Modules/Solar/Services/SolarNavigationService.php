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
                'description' => 'Visao geral do produto',
            ],
            [
                'label' => 'Clientes',
                'route' => 'solar.customers.index',
                'active' => 'solar.customers.*',
                'description' => 'Base comercial e relacionamento',
            ],
            [
                'label' => 'Projetos',
                'route' => 'solar.projects.index',
                'active' => 'solar.projects.*',
                'description' => 'Implantacoes e consumo energetico',
            ],
            [
                'label' => 'Simulacoes',
                'route' => 'solar.simulations.index',
                'active' => 'solar.simulations.*',
                'description' => 'Analise tecnica e comercial',
            ],
            [
                'label' => 'Orcamentos',
                'route' => 'solar.quotes.index',
                'active' => 'solar.quotes.*',
                'description' => 'Propostas e itens comerciais',
            ],
            [
                'label' => 'Configuracoes',
                'route' => 'solar.settings.edit',
                'active' => 'solar.settings.*',
                'description' => 'Custos, margens e padroes da empresa',
            ],
        ];
    }
}
