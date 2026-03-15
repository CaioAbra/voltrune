<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\EnergyUtility;
use App\Modules\Solar\Models\SolarCompanySetting;
use App\Modules\Solar\Models\SolarCustomer;
use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Services\EnergyUtilityResolverService;
use App\Modules\Solar\Services\SolarGeocodingService;
use App\Modules\Solar\Services\SolarNavigationService;
use App\Modules\Solar\Services\SolarRadiationService;
use App\Modules\Solar\Services\SolarSimulationService;
use App\Modules\Solar\Services\SolarSizingService;
use App\Support\CurrentCompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
        private readonly SolarSizingService $sizing,
        private readonly EnergyUtilityResolverService $utilityResolver,
        private readonly SolarGeocodingService $geocoding,
        private readonly SolarRadiationService $radiation,
        private readonly SolarSimulationService $simulations,
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
        $newProject = new SolarProject([
            'solar_customer_id' => $selectedCustomerId > 0 ? $selectedCustomerId : null,
            'connection_type' => 'bi',
            'module_power' => $companySetting?->default_module_power ?: 550,
            'inverter_model' => $companySetting?->default_inverter_model,
            'status' => 'draft',
        ]);
        $factorData = $this->radiation->resolveFactorForProject($newProject);
        $pricingContext = $this->sizing->resolveContextualPricePerKwp($companySetting?->price_per_kwp, $newProject->state);

        return view('solar.projects.create', $this->viewData('Novo projeto', [
            'company' => $company,
            'customers' => $customers,
            'utilities' => $utilities,
            'utilityLookup' => $this->utilityResolver->toFrontendLookup($utilities),
            'companySetting' => $companySetting,
            'effectivePricePerKwp' => $pricingContext['value'],
            'pricingReferenceSource' => $pricingContext['source'],
            'regionalPriceLookup' => $this->sizing->regionalPriceLookup(),
            'solarFactorData' => $factorData,
            'residualMinimumCost' => SolarSizingService::MINIMUM_RESIDUAL_ENERGY_COST,
            'usesMarketPriceFallback' => $pricingContext['source'] !== 'company',
            'project' => $newProject,
        ]));
    }

    public function show(Request $request, int $project): View
    {
        $company = $this->resolveCurrentCompany($request);
        $projectRecord = $this->resolveProject($company, $project, ['customer', 'simulations.quotes', 'quotes.simulation', 'quotes.items']);
        $companySetting = $this->companySetting($company);
        $projectRecord = $this->hydrateProjectAutomationState($projectRecord, $companySetting, false);
        $pricingContext = $this->sizing->resolveContextualPricePerKwp($companySetting?->price_per_kwp, $projectRecord->state);
        $solarFactorData = $this->radiation->resolveFactorForProject($projectRecord);

        return view('solar.projects.show', $this->viewData('Projeto solar', [
            'company' => $company,
            'project' => $projectRecord,
            'defaultSimulation' => $projectRecord->simulations->sortByDesc('id')->values()->first(),
            'simulations' => $projectRecord->simulations->sortByDesc('id')->values(),
            'quotes' => $projectRecord->quotes,
            'companySetting' => $companySetting,
            'effectivePricePerKwp' => $pricingContext['value'],
            'pricingReferenceSource' => $pricingContext['source'],
            'solarFactorData' => $solarFactorData,
            'residualMinimumCost' => SolarSizingService::MINIMUM_RESIDUAL_ENERGY_COST,
            'usesMarketPriceFallback' => $pricingContext['source'] !== 'company',
            'estimatedRequiredPowerKwp' => $this->sizing->estimateRequiredPowerKwp($projectRecord->monthly_consumption_kwh, $projectRecord->solar_factor_used),
            'suggestedModuleQuantity' => $this->sizing->estimateModuleQuantity($projectRecord->system_power_kwp, $projectRecord->module_power),
            'suggestedGenerationKwh' => $this->sizing->estimateGenerationKwh($projectRecord->system_power_kwp, $projectRecord->solar_factor_used),
            'suggestedCommercialPrice' => $this->sizing->estimateSuggestedPrice(
                $projectRecord->system_power_kwp,
                $pricingContext['value'],
            ),
            'estimatedMonthlySavings' => $this->sizing->estimateMonthlySavings($projectRecord->energy_bill_value),
            'estimatedAnnualSavings' => $this->sizing->estimateAnnualSavings($projectRecord->energy_bill_value),
            'estimatedLifetimeSavings' => $this->sizing->estimateLifetimeSavings($projectRecord->energy_bill_value),
            'estimatedAreaSquareMeters' => $this->sizing->estimateAreaSquareMeters($projectRecord->system_power_kwp),
            'estimatedPaybackMonths' => $this->sizing->estimatePaybackMonths($projectRecord->suggested_price ?: $this->sizing->estimateSuggestedPrice(
                $projectRecord->system_power_kwp,
                $pricingContext['value'],
            ), $projectRecord->energy_bill_value),
            'estimatedRoiPercentage' => $this->sizing->estimateRoiPercentage($projectRecord->suggested_price ?: $this->sizing->estimateSuggestedPrice(
                $projectRecord->system_power_kwp,
                $pricingContext['value'],
            ), $projectRecord->energy_bill_value),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $companySetting = $this->companySetting($company);
        $preparedData = $this->prepareLocationData($this->validatedData($request, $company));
        $pricingContext = $this->sizing->resolveContextualPricePerKwp($companySetting?->price_per_kwp, $preparedData['state'] ?? null);
        $data = $this->sizing->applySuggestedSizing(
            $preparedData,
            $companySetting,
            $preparedData['solar_factor_used'] ?? null,
            $pricingContext['value'],
        );
        $data['company_id'] = $company->id;

        $projectRecord = SolarProject::create($data);
        $projectRecord = $this->refreshProjectRadiationAndSizing($projectRecord, $companySetting);
        $this->simulations->createSnapshotForProject($projectRecord, $companySetting);

        return redirect()
            ->route('solar.projects.index')
            ->with('solar_status', 'Projeto criado com sucesso.');
    }

    public function automationPreview(Request $request): JsonResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $companySetting = $this->companySetting($company);
        $projectRecord = null;
        $projectId = $request->integer('project_id');

        if ($projectId > 0) {
            $projectRecord = $this->resolveProject($company, $projectId);
        }

        $data = $request->validate([
            'project_id' => ['nullable', 'integer'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'street' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:2'],
            'monthly_consumption_kwh' => ['nullable', 'numeric', 'min:0'],
            'energy_bill_value' => ['nullable', 'numeric', 'min:0'],
            'module_power' => ['nullable', 'integer', 'min:1'],
            'module_quantity' => ['nullable', 'integer', 'min:1'],
            'system_power_kwp' => ['nullable', 'numeric', 'min:0'],
            'estimated_generation_kwh' => ['nullable', 'numeric', 'min:0'],
            'suggested_price' => ['nullable', 'numeric', 'min:0'],
            'inverter_model' => ['nullable', 'string', 'max:255'],
            'energy_utility_id' => ['nullable', 'integer'],
            'connection_type' => ['nullable', 'in:mono,bi,tri'],
            'pricing_notes' => ['nullable', 'string'],
        ]);

        $preparedData = $this->prepareLocationData($data, $projectRecord);
        $previewProject = new SolarProject(array_merge(
            $projectRecord?->only([
                'latitude',
                'longitude',
                'geocoding_status',
                'geocoding_precision',
                'solar_factor_used',
                'solar_factor_source',
                'solar_factor_fetched_at',
                'radiation_status',
                'monthly_consumption_kwh',
                'module_power',
                'module_quantity',
                'inverter_model',
                'system_power_kwp',
                'estimated_generation_kwh',
                'suggested_price',
                'state',
            ]) ?? [],
            $preparedData,
        ));

        $previewProject = $this->hydrateProjectAutomationState($previewProject, $companySetting, false);
        $pricingContext = $this->sizing->resolveContextualPricePerKwp($companySetting?->price_per_kwp, $previewProject->state);
        $solarFactorData = $this->radiation->resolveFactorForProject($previewProject);

        return response()->json([
            'latitude' => $previewProject->latitude !== null ? (float) $previewProject->latitude : null,
            'longitude' => $previewProject->longitude !== null ? (float) $previewProject->longitude : null,
            'geocoding_status' => $previewProject->geocoding_status,
            'geocoding_precision' => $previewProject->geocoding_precision,
            'geocoding_precision_label' => match ($previewProject->geocoding_precision ?: 'fallback') {
                'address' => 'Endereco refinado',
                'city' => 'Cidade aproximada',
                default => 'Fallback padrao',
            },
            'solar_factor' => round((float) ($previewProject->solar_factor_used ?: SolarSizingService::DEFAULT_SOLAR_FACTOR), 2),
            'solar_factor_source' => $solarFactorData['source'],
            'solar_factor_source_label' => strtoupper(($solarFactorData['source'] ?? 'fallback') === 'pvgis' ? 'PVGIS' : 'PADRAO'),
            'solar_factor_message' => $solarFactorData['message'],
            'pricing_per_kwp' => $pricingContext['value'],
            'pricing_source' => $pricingContext['source'],
            'energy_utility_id' => $preparedData['energy_utility_id'] ?? null,
            'utility_company' => $preparedData['utility_company'] ?? null,
        ]);
    }

    public function edit(Request $request, int $project): View
    {
        $company = $this->resolveCurrentCompany($request);
        $projectRecord = $this->resolveProject($company, $project);
        $utilities = $this->utilityOptions();
        $companySetting = $this->companySetting($company);
        $projectRecord = $this->hydrateProjectAutomationState($projectRecord, $companySetting, false);
        $pricingContext = $this->sizing->resolveContextualPricePerKwp($companySetting?->price_per_kwp, $projectRecord->state);
        $solarFactorData = $this->radiation->resolveFactorForProject($projectRecord);

        return view('solar.projects.edit', $this->viewData('Editar projeto', [
            'company' => $company,
            'customers' => $this->customerOptions($company),
            'utilities' => $utilities,
            'utilityLookup' => $this->utilityResolver->toFrontendLookup($utilities),
            'companySetting' => $companySetting,
            'effectivePricePerKwp' => $pricingContext['value'],
            'pricingReferenceSource' => $pricingContext['source'],
            'regionalPriceLookup' => $this->sizing->regionalPriceLookup(),
            'solarFactorData' => $solarFactorData,
            'residualMinimumCost' => SolarSizingService::MINIMUM_RESIDUAL_ENERGY_COST,
            'usesMarketPriceFallback' => $pricingContext['source'] !== 'company',
            'project' => $projectRecord,
        ]));
    }

    public function update(Request $request, int $project): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $projectRecord = $this->resolveProject($company, $project);
        $companySetting = $this->companySetting($company);
        $preparedData = $this->prepareLocationData($this->validatedData($request, $company), $projectRecord);
        $pricingContext = $this->sizing->resolveContextualPricePerKwp($companySetting?->price_per_kwp, $preparedData['state'] ?? null);

        $projectRecord->update(
            $this->sizing->applySuggestedSizing(
                $preparedData,
                $companySetting,
                $preparedData['solar_factor_used'] ?? $projectRecord->solar_factor_used,
                $pricingContext['value'],
            )
        );
        $projectRecord = $this->refreshProjectRadiationAndSizing($projectRecord->refresh(), $companySetting);

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

        $company = CurrentCompanyContext::resolve($user, $request->session());

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
    private function prepareLocationData(array $data, ?SolarProject $project = null): array
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
        $currentLocationData = $project?->only([
            'zip_code',
            'street',
            'number',
            'district',
            'city',
            'state',
        ]);

        $selectedUtility = $data['energy_utility_id'] !== null
            ? EnergyUtility::query()->find($data['energy_utility_id'])
            : null;

        if (
            $selectedUtility instanceof EnergyUtility
            && ! $this->utilityResolver->matchesLocation($selectedUtility, $data['city'] ?? null, $data['state'] ?? null)
        ) {
            $selectedUtility = null;
            $data['energy_utility_id'] = null;
        }

        if ($selectedUtility === null && ! empty($data['city']) && ! empty($data['state'])) {
            $selectedUtility = $this->utilityResolver->resolveUtilityByCity($data['city'], $data['state']);
            $data['energy_utility_id'] = $selectedUtility?->id;
        }

        $data['utility_company'] = $selectedUtility?->name;

        $data['geocoding_status'] = match (true) {
            empty($data['zip_code']) && empty($data['city']) && empty($data['state']) => 'not_requested',
            $hasAddressLookupData => 'pending',
            default => 'pending',
        };
        $data['geocoding_precision'] = $project?->geocoding_precision ?: 'fallback';
        $data['solar_factor_used'] = $project?->solar_factor_used;
        $data['solar_factor_source'] = $project?->solar_factor_source;
        $data['solar_factor_fetched_at'] = $project?->solar_factor_fetched_at;
        $data['radiation_status'] = $project?->radiation_status;
        $data['address'] = collect([
            $data['street'] ?: null,
            $data['number'] ?: null,
            $data['complement'] ?: null,
            $data['district'] ?: null,
            $data['city'] ?: null,
            $data['state'] ?: null,
        ])->filter()->implode(', ');

        $coordinatesMissing = $project?->latitude === null || $project?->longitude === null;

        if ($this->geocoding->shouldRefreshCoordinates($data, $currentLocationData, $coordinatesMissing, $project?->geocoding_precision)) {
            $coordinates = $this->geocoding->resolveCoordinates($data, [
                'latitude' => $project?->latitude !== null ? (float) $project->latitude : null,
                'longitude' => $project?->longitude !== null ? (float) $project->longitude : null,
            ], $project?->geocoding_precision);
            $data['latitude'] = $coordinates['latitude'];
            $data['longitude'] = $coordinates['longitude'];
            $data['geocoding_status'] = $coordinates['status'];
            $data['geocoding_precision'] = $coordinates['precision'];

            $coordinatesChanged = round((float) ($project?->latitude ?? 0), 6) !== round((float) ($coordinates['latitude'] ?? 0), 6)
                || round((float) ($project?->longitude ?? 0), 6) !== round((float) ($coordinates['longitude'] ?? 0), 6);
            $precisionChanged = $this->geocoding->normalizePrecision($project?->geocoding_precision) !== $coordinates['precision'];

            if ($coordinatesChanged || $precisionChanged) {
                $data['solar_factor_used'] = null;
                $data['solar_factor_source'] = null;
                $data['solar_factor_fetched_at'] = null;
                $data['radiation_status'] = null;
            }
        } else {
            $data['latitude'] = $project?->latitude;
            $data['longitude'] = $project?->longitude;
            $data['geocoding_status'] = $project?->geocoding_status
                ?: (($project?->latitude !== null && $project?->longitude !== null) ? 'ready' : $data['geocoding_status']);
            $data['geocoding_precision'] = $project?->geocoding_precision ?: $this->geocoding->normalizePrecision(null);
        }

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
            ->with('marginRanges')
            ->where('company_id', $company->id)
            ->first();
    }

    private function refreshProjectRadiationAndSizing(SolarProject $project, ?SolarCompanySetting $companySetting): SolarProject
    {
        return $this->hydrateProjectAutomationState($project, $companySetting, true);
    }

    private function hydrateProjectAutomationState(SolarProject $project, ?SolarCompanySetting $companySetting, bool $persist): SolarProject
    {
        $factorData = $this->radiation->resolveFactorForProject($project);

        $project->forceFill([
            'solar_factor_used' => $factorData['factor'],
            'solar_factor_source' => $factorData['source'],
            'solar_factor_fetched_at' => $factorData['fetched_at'],
            'radiation_status' => $factorData['status'],
        ]);

        $pricingContext = $this->sizing->resolveContextualPricePerKwp($companySetting?->price_per_kwp, $project->state);

        $payload = $project->only([
            'monthly_consumption_kwh',
            'state',
            'module_power',
            'module_quantity',
            'inverter_model',
            'system_power_kwp',
            'estimated_generation_kwh',
            'suggested_price',
            'solar_factor_used',
        ]);

        $project->fill(
            $this->sizing->applySuggestedSizing(
                $payload,
                $companySetting,
                $factorData['factor'],
                $pricingContext['value'],
            )
        );

        if ($persist && $project->isDirty()) {
            $project->save();
        }

        return $persist ? $project->refresh() : $project;
    }
}
