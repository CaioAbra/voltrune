<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Solar\Services\SolarNavigationService;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
    ) {
    }

    public function index(): View
    {
        return view('solar.projects.index', [
            'pageTitle' => 'Projetos',
            'pageDescription' => 'Area reservada para organizar cada operacao solar por cliente, endereco e consumo.',
            'navigationItems' => $this->navigation->items(),
        ]);
    }
}
