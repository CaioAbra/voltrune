<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\EnergyUtility;
use App\Modules\Solar\Models\SolarCompanySetting;
use App\Modules\Solar\Models\SolarCustomer;
use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Services\EnergyUtilityResolverService;
use App\Modules\Solar\Services\SolarNavigationService;
use App\Modules\Solar\Services\SolarSizingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
        private readonly SolarSizingService $sizing,
        private readonly EnergyUtilityResolverService $utilityResolver,
    ) {
    }

    public function index(Request $request): View
    {
        $company = $this->resolveCurrentCompany($request);
        $projects = SolarProject::query()
            ->with('customer')
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get();

        return view('solar.projects.index', $this->viewData('Projetos', [
            'company' => $company,
            'projects' => $projects,
        ]));
    }

    public function create(Request $request): View
    {
        $company = $this->resolveCurrentCompany($request);
        $customers = $this->customerOptions($company);
        $utilities = $this->utilityOptions();
        $selectedCustomerId = $request->integer('customer');
        $companySetting = $this->companySetting($company);
        $effectivePricePerKwp = $this->sizing->resolvePricePerKwp($companySetting?->price_per_kwp);

        return view('solar.projects.create', $this->viewData('Novo projeto', [
            'company' => $company,
            'customers' => $customers,
            'utilities' => $utilities,
            'utilityLookup' => $this->utilityResolver->toFrontendLookup($utilities),
            'companySetting' => $companySetting,
            'effectivePricePerKwp' => $effectivePricePerKwp,
            'residualMinimumCost' => SolarSizingService::MINIMUM_RESIDUAL_ENERGY_COST,
            'usesMarketPriceFallback' => ! $companySetting?->price_per_kwp,
            'project' => new SolarProject([
                'solar_customer_id' => $selectedCustomerId > 0 ? $selectedCustomerId : null,
                'connection_type' => 'bi',
                'module_power' => $companySetting?->default_module_power ?: 550,
                'inverter_model' => $companySetting?->default_inverter_model,
                'status' => 'draft',
            ]),
        ]));
    }

    public function show(Request $request, int $project): View
    {
        $company = $this->resolveCurrentCompany($request);
        $projectRecord = $this->resolveProject($company, $project, ['customer']);
        $companySetting = $this->companySetting($company);
        $effectivePricePerKwp = $this->sizing->resolvePricePerKwp($companySetting?->price_per_kwp);

        return view('solar.projects.show', $this->viewData('Projeto solar', [
            'company' => $company,
            'project' => $projectRecord,
            'companySetting' => $companySetting,
            'effectivePricePerKwp' => $effectivePricePerKwp,
            'residualMinimumCost' => SolarSizingService::MINIMUM_RESIDUAL_ENERGY_COST,
            'usesMarketPriceFallback' => ! $companySetting?->price_per_kwp,
            'estimatedRequiredPowerKwp' => $this->sizing->estimateRequiredPowerKwp($projectRecord->monthly_consumption_kwh),
            'suggestedModuleQuantity' => $this->sizing->estimateModuleQuantity($projectRecord->system_power_kwp, $projectRecord->module_power),
            'suggestedGenerationKwh' => $this->sizing->estimateGenerationKwh($projectRecord->system_power_kwp),
            'suggestedCommercialPrice' => $this->sizing->estimateSuggestedPrice(
                $projectRecord->system_power_kwp,
                $effectivePricePerKwp,
            ),
            'estimatedMonthlySavings' => $this->sizing->estimateMonthlySavings($projectRecord->energy_bill_value),
            'estimatedAnnualSavings' => $this->sizing->estimateAnnualSavings($projectRecord->energy_bill_value),
            'estimatedLifetimeSavings' => $this->sizing->estimateLifetimeSavings($projectRecord->energy_bill_value),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $companySetting = $this->companySetting($company);
        $data = $this->sizing->applySuggestedSizing(
            $this->prepareLocationData($this->validatedData($request, $company)),
            $companySetting,
        );
        $data['company_id'] = $company->id;

        SolarProject::create($data);

        return redirect()
            ->route('solar.projects.index')
            ->with('solar_status', 'Projeto criado com sucesso.');
    }

    public function edit(Request $request, int $project): View
    {
        $company = $this->resolveCurrentCompany($request);
        $projectRecord = $this->resolveProject($company, $project);
        $utilities = $this->utilityOptions();
        $companySetting = $this->companySetting($company);
        $effectivePricePerKwp = $this->sizing->resolvePricePerKwp($companySetting?->price_per_kwp);

        return view('solar.projects.edit', $this->viewData('Editar projeto', [
            'company' => $company,
            'customers' => $this->customerOptions($company),
            'utilities' => $utilities,
            'utilityLookup' => $this->utilityResolver->toFrontendLookup($utilities),
            'companySetting' => $companySetting,
            'effectivePricePerKwp' => $effectivePricePerKwp,
            'residualMinimumCost' => SolarSizingService::MINIMUM_RESIDUAL_ENERGY_COST,
            'usesMarketPriceFallback' => ! $companySetting?->price_per_kwp,
            'project' => $projectRecord,
        ]));
    }

    public function update(Request $request, int $project): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $projectRecord = $this->resolveProject($company, $project);
        $companySetting = $this->companySetting($company);

        $projectRecord->update(
            $this->sizing->applySuggestedSizing(
                $this->prepareLocationData($this->validatedData($request, $company)),
                $companySetting,
            )
        );

        return redirect()
            ->route('solar.projects.index')
            ->with('solar_status', 'Projeto atualizado com sucesso.');
    }

    public function destroy(Request $request, int $project): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $projectRecord = $this->resolveProject($company, $project);

        $projectRecord->delete();

        return redirect()
            ->route('solar.projects.index')
            ->with('solar_status', 'Projeto excluido com sucesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(string $pageTitle, array $data = []): array
    {
        return array_merge([
            'pageTitle' => $pageTitle,
            'pageDescription' => 'Fluxo de pré-orçamento rápido para instaladores: cliente, local, consumo, sistema sugerido e valor comercial na mesma jornada.',
            'navigationItems' => $this->navigation->items(),
        ], $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, Company $company): array
    {
        return $request->validate([
            'solar_customer_id' => [
                'required',
                'integer',
                'exists:solar_mysql.solar_customers,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($company): void {
                    $belongsToCompany = SolarCustomer::query()
                        ->where('company_id', $company->id)
                        ->whereKey($value)
                        ->exists();

                    if (! $belongsToCompany) {
                        $fail('Selecione um cliente valido da empresa ativa.');
                    }
                },
            ],
            'name' => ['required', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:2'],
            'monthly_consumption_kwh' => ['nullable', 'numeric', 'min:0'],
            'energy_bill_value' => ['nullable', 'numeric', 'min:0'],
            'connection_type' => ['nullable', 'in:mono,bi,tri'],
            'energy_utility_id' => ['nullable', 'integer', 'exists:solar_mysql.energy_utilities,id'],
            'system_power_kwp' => ['nullable', 'numeric', 'min:0'],
            'module_power' => ['nullable', 'integer', 'min:1'],
            'module_quantity' => ['nullable', 'integer', 'min:1'],
            'inverter_model' => ['nullable', 'string', 'max:255'],
            'estimated_generation_kwh' => ['nullable', 'numeric', 'min:0'],
            'suggested_price' => ['nullable', 'numeric', 'min:0'],
            'pricing_notes' => ['nullable', 'string'],
            'property_type' => ['nullable', 'string', 'max:255'],
            'utility_company' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'string', 'max:50'],
        ]);
    }

    private function resolveCurrentCompany(Request $request): Company
    {
        $user = $request->user();

        abort_unless($user, 403);

        $company = $user->companies()
            ->orderByDesc('company_user.is_owner')
            ->first();

        abort_unless($company instanceof Company, 403, 'Empresa ativa nao encontrada.');

        return $company;
    }

    private function resolveProject(Company $company, int $projectId, array $with = []): SolarProject
    {
        return SolarProject::query()
            ->with($with)
            ->where('company_id', $company->id)
            ->findOrFail($projectId);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function prepareLocationData(array $data): array
    {
        $data['zip_code'] = isset($data['zip_code']) ? preg_replace('/\D+/', '', (string) $data['zip_code']) : null;
        $data['street'] = isset($data['street']) ? trim((string) $data['street']) : null;
        $data['number'] = isset($data['number']) ? trim((string) $data['number']) : null;
        $data['complement'] = isset($data['complement']) ? trim((string) $data['complement']) : null;
        $data['district'] = isset($data['district']) ? trim((string) $data['district']) : null;
        $data['city'] = isset($data['city']) ? trim((string) $data['city']) : null;
        $data['state'] = isset($data['state']) ? strtoupper(trim((string) $data['state'])) : null;
        $data['connection_type'] = isset($data['connection_type']) && $data['connection_type'] !== ''
            ? trim((string) $data['connection_type'])
            : null;
        $data['inverter_model'] = isset($data['inverter_model']) ? trim((string) $data['inverter_model']) : null;
        $data['pricing_notes'] = isset($data['pricing_notes']) ? trim((string) $data['pricing_notes']) : null;
        $data['energy_utility_id'] = isset($data['energy_utility_id']) && $data['energy_utility_id'] !== ''
            ? (int) $data['energy_utility_id']
            : null;

        $hasAddressLookupData = ! empty($data['street']) || ! empty($data['district']) || ! empty($data['city']) || ! empty($data['state']);

        if ($data['energy_utility_id'] === null && ! empty($data['city']) && ! empty($data['state'])) {
            $resolvedUtility = $this->utilityResolver->resolveUtilityByCity($data['city'], $data['state']);
            $data['energy_utility_id'] = $resolvedUtility?->id;
        }

        $selectedUtility = $data['energy_utility_id'] !== null
            ? EnergyUtility::query()->find($data['energy_utility_id'])
            : null;

        $data['utility_company'] = $selectedUtility?->name;

        $data['geocoding_status'] = match (true) {
            empty($data['zip_code']) => 'not_requested',
            $hasAddressLookupData => 'address_loaded',
            default => 'pending',
        };
        $data['latitude'] = null;
        $data['longitude'] = null;
        $data['address'] = collect([
            $data['street'] ?: null,
            $data['number'] ?: null,
            $data['complement'] ?: null,
            $data['district'] ?: null,
            $data['city'] ?: null,
            $data['state'] ?: null,
        ])->filter()->implode(', ');

        return $data;
    }

    private function customerOptions(Company $company)
    {
        return SolarCustomer::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function utilityOptions()
    {
        return EnergyUtility::query()
            ->orderBy('state')
            ->orderBy('name')
            ->get(['id', 'name', 'state', 'cities_json']);
    }

    private function companySetting(Company $company): ?SolarCompanySetting
    {
        return SolarCompanySetting::query()
            ->where('company_id', $company->id)
            ->first();
    }
}
