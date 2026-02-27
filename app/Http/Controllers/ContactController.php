<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:160'],
            'whatsapp' => ['required', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
            'company_website' => ['nullable', 'max:0'],
        ]);

        if (! empty($validated['company_website'] ?? null)) {
            return back()->with('status', 'Recebemos sua mensagem e retornaremos em breve.');
        }

        unset($validated['company_website']);

        Log::info('voltrune.contact', [
            ...$validated,
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'received_at' => now()->toIso8601String(),
        ]);

        return back()->with('status', 'Mensagem enviada com sucesso. Sua missão foi registrada.');
    }
}
