<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\SolarCustomer;
use App\Modules\Solar\Services\SolarNavigationService;
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
        $customers = SolarCustomer::query()
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get();

        return view('solar.customers.index', $this->viewData('Clientes', [
            'customers' => $customers,
            'company' => $company,
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

        $company = $user->companies()
            ->orderByDesc('company_user.is_owner')
            ->first();

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
