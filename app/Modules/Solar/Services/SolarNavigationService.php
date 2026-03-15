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
                'description' => 'Inicio do fluxo e proximos passos',
            ],
            [
                'label' => 'Clientes',
                'route' => 'solar.customers.index',
                'active' => 'solar.customers.*',
                'description' => 'Base do atendimento comercial',
            ],
            [
                'label' => 'Projetos',
                'route' => 'solar.projects.index',
                'active' => 'solar.projects.*',
                'description' => 'Local, consumo e dados base',
            ],
            [
                'label' => 'Simulacoes',
                'route' => 'solar.simulations.index',
                'active' => 'solar.simulations.*',
                'description' => 'Revisao tecnica e comercial',
            ],
            [
                'label' => 'Catalogo',
                'route' => 'solar.catalog.index',
                'active' => 'solar.catalog.*',
                'description' => 'Equipamentos, servicos e custos base',
            ],
            [
                'label' => 'Orcamentos',
                'route' => 'solar.quotes.index',
                'active' => 'solar.quotes.*',
                'description' => 'Itens, preco final e status',
            ],
            [
                'label' => 'Configuracoes',
                'route' => 'solar.settings.edit',
                'active' => 'solar.settings.*',
                'description' => 'Padroes comerciais da empresa',
            ],
        ];
    }
}
