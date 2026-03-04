<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    private const SUBJECT_OPTIONS = [
        'Websites e Landings',
        'Apps e Dashboards',
        'Tráfego Pago e Mídia',
        'Marca, Banner e Logo',
        'Hospedagem e Manutenção',
        'Vigilante Jurídico',
        'Outro',
    ];

    public function store(Request $request): RedirectResponse
    {
        if (filled((string) $request->input('company_website', ''))) {
            return back()->with('status', 'Recebemos seu envio. Retornaremos em breve.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:160'],
            'whatsapp' => ['required', 'string', 'max:30'],
            'subject' => ['required', 'string', Rule::in(self::SUBJECT_OPTIONS)],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $inbox = (string) env('CONTACT_INBOX_ADDRESS', env('MAIL_FROM_ADDRESS', 'contato@voltrune.com'));

        Log::info('voltrune.contact', [
            ...$validated,
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'received_at' => now()->toIso8601String(),
        ]);

        try {
            Mail::raw(
                implode(PHP_EOL, [
                    'Nova mensagem pelo formulário da Voltrune',
                    '',
                    'Nome: '.$validated['name'],
                    'Email: '.$validated['email'],
                    'WhatsApp: '.$validated['whatsapp'],
                    'Assunto: '.$validated['subject'],
                    '',
                    'Mensagem:',
                    $validated['message'],
                    '',
                    'IP: '.$request->ip(),
                    'Recebido em: '.now()->format('Y-m-d H:i:s'),
                ]),
                function ($message) use ($inbox, $validated): void {
                    $message
                        ->to($inbox)
                        ->replyTo($validated['email'], $validated['name'])
                        ->subject('[Voltrune] '.$validated['subject']);
                }
            );
        } catch (Throwable $exception) {
            Log::error('voltrune.contact_mail_failed', [
                'message' => $exception->getMessage(),
                'inbox' => $inbox,
            ]);
        }

        return back()->with('status', 'Briefing recebido com sucesso. Sua missão entrou na fila de análise.');
    }
}
