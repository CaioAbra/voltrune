<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Support\CurrentCompanyContext;
use App\Support\HubAdminAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProductAccessIsActive
{
    public function handle(Request $request, Closure $next, string $productKey): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('hub.login');
        }

        if (HubAdminAccess::isAdmin($user)) {
            return $next($request);
        }

        abort_unless(in_array($productKey, Company::PRODUCT_KEYS, true), 404);

        $company = CurrentCompanyContext::resolve($user, $request->session());

        if (! $company || $company->status !== 'active') {
            return redirect()->route('hub.activation-pending');
        }

        $hasAccess = $company->productAccesses()
            ->where('product_key', $productKey)
            ->where('access_status', 'active')
            ->exists();

        abort_unless($hasAccess, 403, 'Acesso ao produto não liberado para esta empresa.');

        return $next($request);
    }
}
