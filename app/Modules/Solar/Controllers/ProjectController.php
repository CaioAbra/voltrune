<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\SolarCustomer;
use App\Modules\Solar\Models\SolarProject;
use App\Modules\Solar\Services\SolarNavigationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
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
        $selectedCustomerId = $request->integer('customer');

        return view('solar.projects.create', $this->viewData('Novo projeto', [
            'company' => $company,
            'customers' => $customers,
            'project' => new SolarProject([
                'solar_customer_id' => $selectedCustomerId > 0 ? $selectedCustomerId : null,
                'status' => 'draft',
            ]),
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $data = $this->prepareLocationData($this->validatedData($request, $company));
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

        return view('solar.projects.edit', $this->viewData('Editar projeto', [
            'company' => $company,
            'customers' => $this->customerOptions($company),
            'project' => $projectRecord,
        ]));
    }

    public function update(Request $request, int $project): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $projectRecord = $this->resolveProject($company, $project);

        $projectRecord->update($this->prepareLocationData($this->validatedData($request, $company)));

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
            'pageDescription' => 'Cada projeto representa o local da instalacao solar. O CEP e os dados do imovel preparam a base para geocodificacao e simulacao futura.',
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

    private function resolveProject(Company $company, int $projectId): SolarProject
    {
        return SolarProject::query()
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

        $hasAddressLookupData = ! empty($data['street']) || ! empty($data['district']) || ! empty($data['city']) || ! empty($data['state']);

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
}
