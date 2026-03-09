<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Solar\Services\SolarNavigationService;
use Illuminate\View\View;

class SolarDashboardController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
    ) {
    }

    public function index(): View
    {
        return view('solar.dashboard', [
            'pageTitle' => 'Dashboard Solar',
            'navigationItems' => $this->navigation->items(),
        ]);
    }
}
