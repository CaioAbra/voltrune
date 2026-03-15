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
        $simulations = SolarSimulation::query()
            ->with(['project.customer'])
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        return view('solar.simulations.index', [
            'pageTitle' => 'Simulacoes',
            'pageDescription' => 'Espaco do produto para estruturar simulacoes tecnicas e comparativos comerciais.',
            'navigationItems' => $this->navigation->items(),
            'simulations' => $simulations,
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
            'pageDescription' => 'Ajuste o cenario tecnico e comercial sem alterar a base do projeto.',
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
