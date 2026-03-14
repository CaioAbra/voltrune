<?php

namespace App\Http\Controllers;

use App\Support\CurrentCompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrentCompanyController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user, 403);

        $data = $request->validate([
            'company_id' => ['required', 'integer'],
        ]);

        $company = CurrentCompanyContext::remember($user, $request->session(), (int) $data['company_id']);

        abort_unless($company !== null, 403, 'Empresa invalida para o usuario autenticado.');

        return back()->with('status', 'Empresa ativa atualizada com sucesso.');
    }
}
