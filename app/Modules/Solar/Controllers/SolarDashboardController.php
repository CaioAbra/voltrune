<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Solar\Models\SolarCustomer;
use App\Modules\Solar\Models\SolarCatalogItem;
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
        $projectStats = null;
        $simulationStats = null;
        $quoteStats = null;

        if ($company) {
            $projectStats = SolarProject::query()
                ->where('company_id', $company->id)
                ->selectRaw("COUNT(*) as total, SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_total")
                ->first();

            $simulationStats = SolarSimulation::query()
                ->leftJoin('solar_quotes', 'solar_quotes.solar_simulation_id', '=', 'solar_simulations.id')
                ->where('solar_simulations.company_id', $company->id)
                ->selectRaw('COUNT(DISTINCT solar_simulations.id) as total')
                ->selectRaw('COUNT(DISTINCT CASE WHEN solar_quotes.id IS NULL THEN solar_simulations.id END) as without_quotes_total')
                ->first();

            $quoteStats = SolarQuote::query()
                ->where('company_id', $company->id)
                ->selectRaw("COUNT(*) as total")
                ->selectRaw("SUM(CASE WHEN status = 'review' THEN 1 ELSE 0 END) as review_total")
                ->selectRaw("SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_total")
                ->selectRaw("SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as won_total")
                ->selectRaw("SUM(CASE WHEN next_contact_at IS NOT NULL AND next_contact_at <= CURRENT_TIMESTAMP THEN 1 ELSE 0 END) as follow_up_due_total")
                ->first();
        }

        return view('solar.dashboard', [
            'pageTitle' => 'Dashboard Solar',
            'pageDescription' => 'Acompanhe o fluxo comercial e operacional do produto Solar dentro da Voltrune.',
            'navigationItems' => $this->navigation->items(),
            'kpis' => [
                'customers' => $company ? SolarCustomer::query()->where('company_id', $company->id)->count() : 0,
                'catalog' => $company ? SolarCatalogItem::query()->where('company_id', $company->id)->where('is_active', true)->count() : 0,
                'projects' => (int) ($projectStats?->total ?? 0),
                'simulations' => (int) ($simulationStats?->total ?? 0),
                'quotes' => (int) ($quoteStats?->total ?? 0),
            ],
            'pipeline' => [
                'projects_draft' => (int) ($projectStats?->draft_total ?? 0),
                'simulations_without_quotes' => (int) ($simulationStats?->without_quotes_total ?? 0),
                'quotes_review' => (int) ($quoteStats?->review_total ?? 0),
                'quotes_sent' => (int) ($quoteStats?->sent_total ?? 0),
                'quotes_won' => (int) ($quoteStats?->won_total ?? 0),
                'quotes_follow_up_due' => (int) ($quoteStats?->follow_up_due_total ?? 0),
            ],
        ]);
    }
}
