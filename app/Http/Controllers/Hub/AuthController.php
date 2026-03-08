<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('hub.dashboard');
        }

        return view('hub.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Credenciais inválidas.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('hub.dashboard'));
    }

    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('hub.dashboard');
        }

        return view('hub.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:hub_mysql.users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        $user = DB::connection('hub_mysql')->transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $company = Company::create([
                'uuid' => (string) Str::uuid(),
                'name' => $data['company_name'],
                'slug' => $this->generateUniqueCompanySlug($data['company_name']),
                'status' => 'pending',
            ]);

            $company->users()->attach($user->id, [
                'role' => 'owner',
                'is_owner' => true,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('hub.activation-pending')
            ->with('status', 'Conta criada com sucesso. Aguarde a ativação da equipe Voltrune.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('hub.login');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('hub.login');
        }

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

        return redirect()->route('hub.account')
            ->with('password_status', 'Senha atualizada com sucesso.');
    }

    private function generateUniqueCompanySlug(string $companyName): string
    {
        $baseSlug = Str::slug($companyName);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'empresa';

        $slug = $baseSlug;
        $suffix = 1;

        while (Company::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
