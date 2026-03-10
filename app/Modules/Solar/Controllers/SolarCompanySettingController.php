<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\SolarCompanySetting;
use App\Modules\Solar\Services\SolarNavigationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SolarCompanySettingController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
    ) {
    }

    public function edit(Request $request): View
    {
        $company = $this->resolveCurrentCompany($request);
        $this->ensureCompanyAdmin($company);

        return view('solar.settings.edit', $this->viewData('Configuracoes comerciais', [
            'company' => $company,
            'setting' => SolarCompanySetting::query()->firstOrNew([
                'company_id' => $company->id,
            ]),
        ]));
    }

    public function update(Request $request): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $this->ensureCompanyAdmin($company);

        $data = $request->validate([
            'default_module_power' => ['nullable', 'integer', 'min:1'],
            'price_per_kwp' => ['nullable', 'numeric', 'min:0'],
            'margin_percent' => ['nullable', 'numeric', 'min:0'],
            'default_inverter_model' => ['nullable', 'string', 'max:255'],
        ]);

        $data['default_inverter_model'] = isset($data['default_inverter_model'])
            ? trim((string) $data['default_inverter_model'])
            : null;

        SolarCompanySetting::query()->updateOrCreate(
            ['company_id' => $company->id],
            $data,
        );

        return redirect()
            ->route('solar.settings.edit')
            ->with('solar_status', 'Configuracoes comerciais atualizadas com sucesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(string $pageTitle, array $data = []): array
    {
        return array_merge([
            'pageTitle' => $pageTitle,
            'pageDescription' => 'Defina os parametros comerciais e tecnicos padrao usados pela sua empresa no fluxo do produto Solar.',
            'navigationItems' => $this->navigation->items(),
        ], $data);
    }

    private function resolveCurrentCompany(Request $request): Company
    {
        $user = $request->user();

        abort_unless($user, 403);

        $company = $user->companies()
            ->orderByDesc('company_user.is_owner')
            ->first();

        abort_unless($company instanceof Company, 403, 'Empresa ativa nao encontrada.');

        return $company;
    }

    private function ensureCompanyAdmin(Company $company): void
    {
        $canManageSettings = (bool) ($company->pivot?->is_owner)
            || ($company->pivot?->role === 'admin');

        abort_unless($canManageSettings, 403, 'Apenas administradores da empresa podem editar as configuracoes do Solar.');
    }
}
