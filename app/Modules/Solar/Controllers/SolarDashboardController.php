<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Solar\Models\SolarCustomer;
use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Models\SolarQuote;
use App\Modules\Solar\Models\SolarSimulation;
use App\Modules\Solar\Services\SolarNavigationService;
use App\Support\CurrentCompanyContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolarDashboardController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
    ) {
    }

    public function index(Request $request): View
    {
        $company = CurrentCompanyContext::resolve($request->user(), $request->session());

        return view('solar.dashboard', [
            'pageTitle' => 'Dashboard Solar',
            'pageDescription' => 'Acompanhe o fluxo comercial e operacional do produto Solar dentro da Voltrune.',
            'navigationItems' => $this->navigation->items(),
            'kpis' => [
                'customers' => $company ? SolarCustomer::query()->where('company_id', $company->id)->count() : 0,
                'projects' => $company ? SolarProject::query()->where('company_id', $company->id)->count() : 0,
                'simulations' => $company ? SolarSimulation::query()->where('company_id', $company->id)->count() : 0,
                'quotes' => $company ? SolarQuote::query()->where('company_id', $company->id)->count() : 0,
            ],
            'pipeline' => [
                'projects_draft' => $company ? SolarProject::query()->where('company_id', $company->id)->where('status', 'draft')->count() : 0,
                'simulations_without_quotes' => $company ? SolarSimulation::query()->where('company_id', $company->id)->doesntHave('quotes')->count() : 0,
                'quotes_review' => $company ? SolarQuote::query()->where('company_id', $company->id)->where('status', 'review')->count() : 0,
                'quotes_sent' => $company ? SolarQuote::query()->where('company_id', $company->id)->where('status', 'sent')->count() : 0,
                'quotes_won' => $company ? SolarQuote::query()->where('company_id', $company->id)->where('status', 'won')->count() : 0,
            ],
        ]);
    }
}
