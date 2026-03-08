<?php

namespace App\Http\Middleware;

use App\Support\HubAdminAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHubAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('hub.login');
        }

        if (! HubAdminAccess::isAdmin(auth()->user())) {
            abort(403, 'Acesso restrito a equipe Voltrune.');
        }

        return $next($request);
    }
}
