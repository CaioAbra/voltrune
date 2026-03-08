<?php

namespace App\Http\Controllers\Hub\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function edit(): View
    {
        return view('hub.admin.account');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        $user = $request->user();
        if (! $user) {
            return redirect()->route('hub.login');
        }

        $user->update([
            'password' => $data['password'],
        ]);

        return redirect()->route('hub.admin.account.edit')
            ->with('password_status', 'Senha atualizada com sucesso.');
    }
}
