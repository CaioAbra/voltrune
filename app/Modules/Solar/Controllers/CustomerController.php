<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\SolarCustomer;
use App\Modules\Solar\Services\SolarNavigationService;
use App\Support\CurrentCompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
    ) {
    }

    public function index(Request $request): View
    {
        $company = $this->resolveCurrentCompany($request);
        $filters = [
            'q' => trim((string) $request->input('q')),
            'state' => strtoupper(trim((string) $request->input('state'))),
            'pipeline' => (string) $request->input('pipeline', ''),
            'sort' => (string) $request->input('sort', 'name_asc'),
        ];
        $allowedPipelines = ['with_projects', 'without_projects'];
        $allowedSorts = ['name_asc', 'recent', 'projects_desc'];

        if (! in_array($filters['pipeline'], $allowedPipelines, true)) {
            $filters['pipeline'] = '';
        }

        if (! in_array($filters['sort'], $allowedSorts, true)) {
            $filters['sort'] = 'name_asc';
        }

        $baseQuery = SolarCustomer::query()
            ->where('company_id', $company->id);

        $stateOptions = (clone $baseQuery)
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->select('state')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');

        $summaryCustomers = (clone $baseQuery)
            ->withCount('projects')
            ->get();

        $customersQuery = (clone $baseQuery)
            ->withCount('projects');

        if ($filters['q'] !== '') {
            $search = '%' . $filters['q'] . '%';

            $customersQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search)
                    ->orWhere('document', 'like', $search)
                    ->orWhere('city', 'like', $search)
                    ->orWhere('state', 'like', $search);
            });
        }

        if ($filters['state'] !== '') {
            $customersQuery->where('state', $filters['state']);
        }

        if ($filters['pipeline'] === 'with_projects') {
            $customersQuery->has('projects');
        } elseif ($filters['pipeline'] === 'without_projects') {
            $customersQuery->doesntHave('projects');
        }

        match ($filters['sort']) {
            'recent' => $customersQuery->latest(),
            'projects_desc' => $customersQuery->orderByDesc('projects_count')->orderBy('name'),
            default => $customersQuery->orderBy('name'),
        };

        $customers = $customersQuery->get();
        $hasActiveFilters = $filters['q'] !== ''
            || $filters['state'] !== ''
            || $filters['pipeline'] !== ''
            || $filters['sort'] !== 'name_asc';

        return view('solar.customers.index', $this->viewData('Clientes', [
            'customers' => $customers,
            'company' => $company,
            'filters' => $filters,
            'stateOptions' => $stateOptions,
            'hasActiveFilters' => $hasActiveFilters,
            'summary' => [
                'total' => $summaryCustomers->count(),
                'with_projects' => $summaryCustomers->filter(fn (SolarCustomer $customer) => $customer->projects_count > 0)->count(),
                'without_projects' => $summaryCustomers->filter(fn (SolarCustomer $customer) => $customer->projects_count === 0)->count(),
                'states' => $summaryCustomers->pluck('state')->filter()->unique()->count(),
                'filtered' => $customers->count(),
            ],
        ]));
    }

    public function create(Request $request): View
    {
        $company = $this->resolveCurrentCompany($request);

        return view('solar.customers.create', $this->viewData('Novo cliente', [
            'customer' => new SolarCustomer(),
            'company' => $company,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $data = $this->validatedData($request);
        $data['company_id'] = $company->id;

        SolarCustomer::create($data);

        return redirect()
            ->route('solar.customers.index')
            ->with('solar_status', 'Cliente criado com sucesso.');
    }

    public function edit(Request $request, int $customer): View
    {
        $company = $this->resolveCurrentCompany($request);
        $customerRecord = $this->resolveCustomer($company, $customer);

        return view('solar.customers.edit', $this->viewData('Editar cliente', [
            'customer' => $customerRecord,
            'company' => $company,
        ]));
    }

    public function update(Request $request, int $customer): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $customerRecord = $this->resolveCustomer($company, $customer);

        $customerRecord->update($this->validatedData($request));

        return redirect()
            ->route('solar.customers.index')
            ->with('solar_status', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Request $request, int $customer): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $customerRecord = $this->resolveCustomer($company, $customer);

        $customerRecord->delete();

        return redirect()
            ->route('solar.customers.index')
            ->with('solar_status', 'Cliente excluido com sucesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(string $pageTitle, array $data = []): array
    {
        return array_merge([
            'pageTitle' => $pageTitle,
            'pageDescription' => 'Gerencie a base de clientes do produto Solar sem sair do contexto operacional do SaaS.',
            'navigationItems' => $this->navigation->items(),
        ], $data);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:2'],
            'notes' => ['nullable', 'string'],
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

    private function resolveCustomer(Company $company, int $customerId): SolarCustomer
    {
        return SolarCustomer::query()
            ->where('company_id', $company->id)
            ->findOrFail($customerId);
    }
}
