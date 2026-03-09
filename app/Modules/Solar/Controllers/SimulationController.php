<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Solar\Services\SolarNavigationService;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
    ) {
    }

    public function index(): View
    {
        return view('solar.simulations.index', [
            'pageTitle' => 'Simulacoes',
            'navigationItems' => $this->navigation->items(),
        ]);
    }
}
