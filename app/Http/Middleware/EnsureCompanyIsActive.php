<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Support\HubAdminAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('hub.login');
        }

        if (HubAdminAccess::isAdmin($user)) {
            return $next($request);
        }

        $company = $this->resolveCurrentCompany($user);

        if (! $company) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('hub.login')->withErrors([
                'email' => 'Conta sem empresa vinculada. Fale com a equipe Voltrune.',
            ]);
        }

        if ($company->status !== 'active') {
            return redirect()->route('hub.activation-pending');
        }

        return $next($request);
    }

    private function resolveCurrentCompany($user): ?Company
    {
        return $user->companies()
            ->orderByDesc('company_user.is_owner')
            ->first();
    }
}

