<?php

namespace App\Http\Controllers\Hub\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Support\HubAdminAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $baseQuery = HubAdminAccess::applyClientCompaniesFilter(Company::query());

        $companyMetrics = [
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'active' => (clone $baseQuery)->where('status', 'active')->count(),
            'suspended' => (clone $baseQuery)->where('status', 'suspended')->count(),
        ];

        $financialMetrics = [
            'pending' => (clone $baseQuery)
                ->where(function (Builder $query): void {
                    $query->whereDoesntHave('latestBillingRecord')
                        ->orWhereHas('latestBillingRecord', function (Builder $billingQuery): void {
                            $billingQuery->where('financial_status', 'pending');
                        });
                })
                ->count(),
            'overdue' => (clone $baseQuery)
                ->whereHas('latestBillingRecord', function (Builder $query): void {
                    $query->where('financial_status', 'overdue');
                })
                ->count(),
        ];

        return view('hub.admin.dashboard', [
            'companyMetrics' => $companyMetrics,
            'financialMetrics' => $financialMetrics,
        ]);
    }
}
