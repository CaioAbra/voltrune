<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('hub.auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::broker('hub_users')->sendResetLink($credentials);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withInput($request->only('email'))->withErrors([
            'email' => __($status),
        ]);
    }
}
