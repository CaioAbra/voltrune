<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Support\CurrentCompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HubController extends Controller
{
    public function dashboard(): View|RedirectResponse
    {
        return $this->renderHubView('hub.dashboard');
    }

    public function products(): View|RedirectResponse
    {
        return $this->renderHubView('hub.products');
    }

    public function account(): View|RedirectResponse
    {
        return $this->renderHubView('hub.account');
    }

    public function billing(): View|RedirectResponse
    {
        return $this->renderHubView('hub.billing');
    }

    public function help(): View|RedirectResponse
    {
        return $this->renderHubView('hub.help');
    }

    public function activationPending(): View|RedirectResponse
    {
        return $this->renderHubView('hub.pending-activation');
    }

    private function renderHubView(string $view): View|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('hub.login');
        }

        $company = $this->resolveCurrentCompany();
        $isCompanyActive = $company?->status === 'active';
        $financialStatus = $this->resolveFinancialStatus($company);
        $productAccess = $this->resolveProductAccess($company, $isCompanyActive);

        return view($view, [
            'company' => $company,
            'companyStatus' => $company?->status ?? 'pending',
            'isCompanyActive' => $isCompanyActive,
            'financialStatus' => $financialStatus,
            'productAccess' => $productAccess,
            'productLabels' => $this->productLabels(),
        ]);
    }

    private function resolveCurrentCompany(): ?Company
    {
        return CurrentCompanyContext::resolve(Auth::user(), request()->session());
    }

    /**
     * @return array<string, bool>
     */
    private function resolveProductAccess(?Company $company, bool $isCompanyActive): array
    {
        $default = collect(Company::PRODUCT_KEYS)
            ->mapWithKeys(static fn (string $key): array => [$key => false])
            ->all();

        if (! $company) {
            return $default;
        }

        $company->loadMissing('productAccesses');

        $productStates = $company->productAccesses
            ->mapWithKeys(static fn ($access): array => [
                $access->product_key => $access->access_status === 'active',
            ])
            ->all();

        $merged = array_replace($default, $productStates);

        if (! $isCompanyActive) {
            return array_map(static fn (): bool => false, $merged);
        }

        return $merged;
    }

    private function resolveFinancialStatus(?Company $company): string
    {
        if (! $company) {
            return 'pending';
        }

        $company->loadMissing('latestBillingRecord');

        return $company->latestBillingRecord?->financial_status ?? 'pending';
    }

    /**
     * @return array<string, string>
     */
    private function productLabels(): array
    {
        return [
            'solar' => 'Solar',
            'vigilante' => 'Vigilante',
            'agro' => 'Agro',
        ];
    }
}
