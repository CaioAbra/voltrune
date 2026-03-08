<?php

namespace App\Http\Controllers\Hub\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Support\HubAdminAccess;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $companies = HubAdminAccess::applyClientCompaniesFilter(
            Company::query()->with('latestBillingRecord')
        )->get();

        $companyMetrics = [
            'pending' => $companies->where('status', 'pending')->count(),
            'active' => $companies->where('status', 'active')->count(),
            'suspended' => $companies->where('status', 'suspended')->count(),
        ];

        $financialMetrics = [
            'pending' => $companies->filter(
                static fn (Company $company): bool => ($company->latestBillingRecord?->financial_status ?? 'pending') === 'pending'
            )->count(),
            'overdue' => $companies->filter(
                static fn (Company $company): bool => ($company->latestBillingRecord?->financial_status ?? 'pending') === 'overdue'
            )->count(),
        ];

        return view('hub.admin.dashboard', [
            'companyMetrics' => $companyMetrics,
            'financialMetrics' => $financialMetrics,
        ]);
    }
}
