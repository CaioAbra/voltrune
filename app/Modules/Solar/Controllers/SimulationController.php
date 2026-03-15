<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\SolarCompanySetting;
use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Models\SolarSimulation;
use App\Modules\Solar\Services\SolarNavigationService;
use App\Modules\Solar\Services\SolarSizingService;
use App\Modules\Solar\Services\SolarSimulationService;
use App\Support\CurrentCompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
        private readonly SolarSizingService $sizing,
        private readonly SolarSimulationService $simulations,
    ) {
    }

    public function index(Request $request): View
    {
        $company = $this->resolveCurrentCompany($request);
        $filters = [
            'q' => trim((string) $request->input('q')),
            'status' => (string) $request->input('status', ''),
            'quote_state' => (string) $request->input('quote_state', ''),
            'sort' => (string) $request->input('sort', 'recent'),
        ];
        $allowedStatuses = ['draft', 'qualified', 'proposal', 'won'];
        $allowedQuoteStates = ['with_quotes', 'without_quotes'];
        $allowedSorts = ['recent', 'name_asc', 'power_desc', 'price_desc', 'savings_desc', 'quotes_desc'];

        if (! in_array($filters['status'], $allowedStatuses, true)) {
            $filters['status'] = '';
        }

        if (! in_array($filters['quote_state'], $allowedQuoteStates, true)) {
            $filters['quote_state'] = '';
        }

        if (! in_array($filters['sort'], $allowedSorts, true)) {
            $filters['sort'] = 'recent';
        }

        $baseQuery = SolarSimulation::query()
            ->with(['project.customer'])
            ->withCount('quotes')
            ->where('company_id', $company->id)
            ;

        $summarySimulations = (clone $baseQuery)->get();
        $simulationsQuery = clone $baseQuery;

        if ($filters['q'] !== '') {
            $search = '%' . $filters['q'] . '%';

            $simulationsQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', $search)
                    ->orWhereHas('project', function ($projectQuery) use ($search): void {
                        $projectQuery->where('name', 'like', $search)
                            ->orWhereHas('customer', function ($customerQuery) use ($search): void {
                                $customerQuery->where('name', 'like', $search);
                            });
                    });
            });
        }

        if ($filters['status'] !== '') {
            $simulationsQuery->where('status', $filters['status']);
        }

        if ($filters['quote_state'] === 'with_quotes') {
            $simulationsQuery->has('quotes');
        } elseif ($filters['quote_state'] === 'without_quotes') {
            $simulationsQuery->doesntHave('quotes');
        }

        match ($filters['sort']) {
            'name_asc' => $simulationsQuery->orderBy('name'),
            'power_desc' => $simulationsQuery->orderByDesc('system_power_kwp')->orderByDesc('id'),
            'price_desc' => $simulationsQuery->orderByDesc('suggested_price')->orderByDesc('id'),
            'savings_desc' => $simulationsQuery->orderByDesc('estimated_monthly_savings')->orderByDesc('id'),
            'quotes_desc' => $simulationsQuery->orderByDesc('quotes_count')->orderByDesc('id'),
            default => $simulationsQuery->latest(),
        };

        $simulations = $simulationsQuery->get();
        $hasActiveFilters = $filters['q'] !== ''
            || $filters['status'] !== ''
            || $filters['quote_state'] !== ''
            || $filters['sort'] !== 'recent';

        return view('solar.simulations.index', [
            'pageTitle' => 'Simulacoes',
            'pageDescription' => 'Revise simulacoes, compare alternativas e decida quando abrir o orcamento.',
            'navigationItems' => $this->navigation->items(),
            'simulations' => $simulations,
            'filters' => $filters,
            'hasActiveFilters' => $hasActiveFilters,
            'summary' => [
                'total' => $summarySimulations->count(),
                'draft' => $summarySimulations->where('status', 'draft')->count(),
                'qualified' => $summarySimulations->where('status', 'qualified')->count(),
                'proposal' => $summarySimulations->where('status', 'proposal')->count(),
                'with_quotes' => $summarySimulations->filter(fn (SolarSimulation $simulation) => $simulation->quotes_count > 0)->count(),
                'filtered' => $simulations->count(),
            ],
        ]);
    }

    public function show(Request $request, int $simulation): View
    {
        $company = $this->resolveCurrentCompany($request);
        $companySetting = SolarCompanySetting::query()
            ->with('marginRanges')
            ->where('company_id', $company->id)
            ->first();
        $simulationRecord = SolarSimulation::query()
            ->with([
                'project.customer',
                'project.energyUtility',
                'quotes' => fn ($query) => $query->latest(),
            ])
            ->where('company_id', $company->id)
            ->findOrFail($simulation);

        return view('solar.simulations.show', [
            'pageTitle' => 'Simulacao',
            'pageDescription' => 'Leitura tecnica e comercial da simulacao solar selecionada.',
            'navigationItems' => $this->navigation->items(),
            'company' => $company,
            'companySetting' => $companySetting,
            'marginContext' => $this->sizing->resolveMarginContext($companySetting, $simulationRecord->system_power_kwp),
            'simulation' => $simulationRecord,
            'project' => $simulationRecord->project,
            'latestQuote' => $simulationRecord->quotes->first(),
            'sizingService' => $this->sizing,
        ]);
    }

    public function storeFromProject(Request $request, int $project): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $projectRecord = SolarProject::query()
            ->where('company_id', $company->id)
            ->findOrFail($project);
        $payload = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);
        $companySetting = SolarCompanySetting::query()
            ->where('company_id', $company->id)
            ->first();
        $simulation = $this->simulations->createSnapshotForProject(
            $projectRecord,
            $companySetting,
            $payload['name'] ?? null,
            $payload['notes'] ?? null,
        );

        return redirect()
            ->route('solar.simulations.show', $simulation->id)
            ->with('solar_status', 'Nova simulacao criada: ' . $simulation->name . '.');
    }

    public function duplicate(Request $request, int $simulation): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $simulationRecord = SolarSimulation::query()
            ->with('project')
            ->where('company_id', $company->id)
            ->findOrFail($simulation);

        $duplicate = $this->simulations->duplicate($simulationRecord);

        return redirect()
            ->route('solar.simulations.show', $duplicate->id)
            ->with('solar_status', 'Simulacao duplicada com sucesso: ' . $duplicate->name . '.');
    }

    public function edit(Request $request, int $simulation): View
    {
        $company = $this->resolveCurrentCompany($request);
        $companySetting = SolarCompanySetting::query()
            ->with('marginRanges')
            ->where('company_id', $company->id)
            ->first();
        $simulationRecord = SolarSimulation::query()
            ->with(['project.customer', 'quotes'])
            ->where('company_id', $company->id)
            ->findOrFail($simulation);

        return view('solar.simulations.edit', [
            'pageTitle' => 'Editar simulacao',
            'pageDescription' => 'Ajuste a leitura tecnica e comercial sem alterar a base do projeto.',
            'navigationItems' => $this->navigation->items(),
            'company' => $company,
            'companySetting' => $companySetting,
            'simulation' => $simulationRecord,
            'project' => $simulationRecord->project,
        ]);
    }

    public function update(Request $request, int $simulation): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $companySetting = SolarCompanySetting::query()
            ->with('marginRanges')
            ->where('company_id', $company->id)
            ->first();
        $simulationRecord = SolarSimulation::query()
            ->with('project')
            ->where('company_id', $company->id)
            ->findOrFail($simulation);
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,qualified,proposal,won'],
            'system_power_kwp' => ['nullable', 'numeric', 'min:0'],
            'module_power' => ['nullable', 'integer', 'min:1'],
            'module_quantity' => ['nullable', 'integer', 'min:1'],
            'estimated_generation_kwh' => ['nullable', 'numeric', 'min:0'],
            'suggested_price' => ['nullable', 'numeric', 'min:0'],
            'payment_mode' => ['nullable', 'in:cash,financed'],
            'upfront_payment' => ['nullable', 'numeric', 'min:0'],
            'installment_count' => ['nullable', 'integer', 'min:1'],
            'monthly_interest_rate' => ['nullable', 'numeric', 'min:0'],
            'tariff_growth_yearly' => ['nullable', 'numeric', 'min:0'],
            'inverter_model' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $simulationRecord->update(array_merge(
            [
                'name' => trim((string) $payload['name']),
            ],
            $this->simulations->rebuildPayload(
                $simulationRecord,
                $simulationRecord->project,
                $companySetting,
                Arr::except($payload, ['name']),
            )
        ));

        return redirect()
            ->route('solar.simulations.show', $simulationRecord->id)
            ->with('solar_status', 'Simulacao atualizada com sucesso.');
    }

    private function resolveCurrentCompany(Request $request): Company
    {
        $user = $request->user();

        abort_unless($user, 403);

        $company = CurrentCompanyContext::resolve($user, $request->session());

        abort_unless($company instanceof Company, 403, 'Empresa ativa nao encontrada.');

        return $company;
    }
}
