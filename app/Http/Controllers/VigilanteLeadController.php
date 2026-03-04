<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VigilanteLeadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (filled((string) $request->input('company_website', ''))) {
            return back()->with('vigilante_status', 'Interesse registrado. Avisaremos quando a abertura estiver pronta.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email:rfc,dns', 'max:160', 'required_without:whatsapp'],
            'whatsapp' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'interest' => ['required', 'string', 'max:300'],
        ]);

        Log::info('voltrune.vigilante_interest', [
            ...$validated,
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'received_at' => now()->toIso8601String(),
        ]);

        return back()->with('vigilante_status', 'Interesse registrado. Você será avisado quando o Vigilante abrir.');
    }
}
