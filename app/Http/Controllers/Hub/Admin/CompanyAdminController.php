<?php

namespace App\Http\Controllers\Hub\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyBillingRecord;
use App\Models\CompanyContract;
use App\Models\CompanyProductAccess;
use App\Support\HubAdminAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompanyAdminController extends Controller
{
    public function index(): View
    {
        return $this->renderIndex('clients');
    }

    public function contracts(): View
    {
        return $this->renderIndex('contracts');
    }

    public function billing(): View
    {
        return $this->renderIndex('billing');
    }

    public function access(): View
    {
        return $this->renderIndex('access');
    }

    private function renderIndex(string $focus): View
    {
        $companyStatus = request('company_status');
        $financialStatus = request('financial_status');
        $productKey = request('product_key');
        $accessState = request('access_state');

        $companies = HubAdminAccess::applyClientCompaniesFilter(
            Company::query()
            ->with(['users' => function ($query): void {
                $query->orderByDesc('company_user.is_owner')->orderBy('users.id');
            }])
            ->with(['billingRecords', 'productAccesses', 'contracts'])
            ->when($companyStatus, function ($query, string $status): void {
                $query->where('status', $status);
            })
            ->when($financialStatus, function ($query, string $status): void {
                if ($status === 'pending') {
                    $query->where(function ($nestedQuery): void {
                        $nestedQuery
                            ->whereDoesntHave('billingRecords')
                            ->orWhereHas('billingRecords', function ($billingQuery): void {
                                $billingQuery->where('financial_status', 'pending');
                            });
                    });

                    return;
                }

                $query->whereHas('billingRecords', function ($billingQuery) use ($status): void {
                    $billingQuery->where('financial_status', $status);
                });
            })
            ->when($productKey, function ($query, string $key): void {
                $query->whereHas('productAccesses', function ($accessQuery) use ($key): void {
                    $accessQuery->where('product_key', $key)->where('access_status', 'active');
                });
            })
            ->when($accessState === 'active', function ($query): void {
                $query->whereHas('productAccesses', function ($accessQuery): void {
                    $accessQuery->where('access_status', 'active');
                });
            })
            ->latest()
        )->get();

        return view('hub.admin.companies.index', [
            'companies' => $companies,
            'allowedStatuses' => $this->allowedStatuses(),
            'allowedFinancialStatuses' => $this->allowedFinancialStatuses(),
            'productLabels' => $this->productLabels(),
            'filters' => [
                'company_status' => $companyStatus,
                'financial_status' => $financialStatus,
                'product_key' => $productKey,
                'access_state' => $accessState,
            ],
            'focus' => $focus,
        ]);
    }

    public function show(Company $company): View
    {
        abort_if(! $company->users()->whereNotIn('users.email', HubAdminAccess::allowedEmails()->all())->exists(), 404);

        $company->load([
            'users' => function ($query): void {
                $query->orderByDesc('company_user.is_owner')->orderBy('users.id');
            },
            'contracts' => function ($query): void {
                $query->orderBy('product_key');
            },
            'productAccesses' => function ($query): void {
                $query->orderBy('product_key');
            },
            'billingRecords',
        ]);

        $contractsByProduct = collect(Company::PRODUCT_KEYS)
            ->mapWithKeys(fn (string $key): array => [$key => $company->contracts->firstWhere('product_key', $key)]);

        $accessByProduct = collect(Company::PRODUCT_KEYS)
            ->mapWithKeys(fn (string $key): array => [$key => $company->productAccesses->firstWhere('product_key', $key)]);

        return view('hub.admin.companies.show', [
            'company' => $company,
            'contractsByProduct' => $contractsByProduct,
            'accessByProduct' => $accessByProduct,
            'latestBilling' => $company->billingRecords->first(),
            'allowedStatuses' => $this->allowedStatuses(),
            'allowedBillingCycles' => $this->allowedBillingCycles(),
            'allowedFinancialStatuses' => $this->allowedFinancialStatuses(),
            'allowedPaymentMethods' => $this->allowedPaymentMethods(),
            'productLabels' => $this->productLabels(),
        ]);
    }

    public function updateStatus(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in($this->allowedStatuses())],
        ]);

        $company->status = $validated['status'];
        $company->save();

        return back()->with('status', "Status da empresa atualizado para {$company->status}.");
    }

    public function upsertContract(Request $request, Company $company, string $productKey): RedirectResponse
    {
        abort_unless(in_array($productKey, Company::PRODUCT_KEYS, true), 404);

        $validated = $request->validate([
            'plan_name' => ['nullable', 'string', 'max:255'],
            'billing_cycle' => ['nullable', 'string', Rule::in($this->allowedBillingCycles())],
            'negotiated_value' => ['nullable', 'numeric', 'min:0'],
            'commercial_notes' => ['nullable', 'string'],
        ]);

        CompanyContract::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'product_key' => $productKey,
            ],
            [
                'plan_name' => $validated['plan_name'] ?: null,
                'billing_cycle' => $validated['billing_cycle'] ?: null,
                'negotiated_value' => $validated['negotiated_value'] ?? null,
                'commercial_notes' => $validated['commercial_notes'] ?: null,
            ]
        );

        return back()->with('status', 'Dados de contratação atualizados.');
    }

    public function upsertAccess(Request $request, Company $company, string $productKey): RedirectResponse
    {
        abort_unless(in_array($productKey, Company::PRODUCT_KEYS, true), 404);

        $validated = $request->validate([
            'access_status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ]);

        CompanyProductAccess::query()->updateOrCreate(
            [
                'company_id' => $company->id,
                'product_key' => $productKey,
            ],
            [
                'access_status' => $validated['access_status'],
            ]
        );

        return back()->with('status', 'Status de acesso atualizado.');
    }

    public function storeBilling(Request $request, Company $company): RedirectResponse
    {
        $validated = $request->validate([
            'financial_status' => ['required', 'string', Rule::in($this->allowedFinancialStatuses())],
            'payment_method' => ['nullable', 'string', Rule::in($this->allowedPaymentMethods())],
            'last_payment_date' => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'next_billing_date' => ['nullable', 'date'],
            'financial_notes' => ['nullable', 'string'],
        ]);

        CompanyBillingRecord::query()->create([
            'company_id' => $company->id,
            'financial_status' => $validated['financial_status'],
            'payment_method' => $validated['payment_method'] ?? null,
            'last_payment_date' => $validated['last_payment_date'] ?? null,
            'paid_amount' => $validated['paid_amount'] ?? null,
            'next_billing_date' => $validated['next_billing_date'] ?? null,
            'financial_notes' => $validated['financial_notes'] ?? null,
        ]);

        return back()->with('status', 'Registro de cobrança salvo.');
    }

    /** @return list<string> */
    private function allowedStatuses(): array
    {
        return ['pending', 'active', 'suspended'];
    }

    /** @return list<string> */
    private function allowedBillingCycles(): array
    {
        return ['monthly', 'yearly', 'custom'];
    }

    /** @return list<string> */
    private function allowedFinancialStatuses(): array
    {
        return ['pending', 'paid', 'overdue', 'canceled'];
    }

    /** @return list<string> */
    private function allowedPaymentMethods(): array
    {
        return ['pix', 'transfer', 'card', 'boleto', 'other'];
    }

    /** @return array<string, string> */
    private function productLabels(): array
    {
        return [
            'solar' => 'Solar',
            'vigilante' => 'Vigilante',
            'agro' => 'Agro',
        ];
    }
}
