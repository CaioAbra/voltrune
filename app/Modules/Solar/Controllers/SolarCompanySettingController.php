<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\SolarCompanySetting;
use App\Modules\Solar\Services\SolarNavigationService;
use App\Modules\Solar\Services\SolarSizingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
            'marketPricePerKwp' => SolarSizingService::MARKET_PRICE_PER_KWP,
            'setting' => $this->companySetting($company),
        ]));
    }

    public function update(Request $request): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $this->ensureCompanyAdmin($company);

        $payload = $this->validatedPayload($request);

        DB::connection('solar_mysql')->transaction(function () use ($company, $payload): void {
            $setting = SolarCompanySetting::query()->updateOrCreate(
                ['company_id' => $company->id],
                $payload['attributes'],
            );

            $setting->marginRanges()->delete();

            if ($payload['attributes']['margin_mode'] === SolarCompanySetting::MARGIN_MODE_RANGE && $payload['margin_ranges'] !== []) {
                $setting->marginRanges()->createMany($payload['margin_ranges']);
            }
        });

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

    private function companySetting(Company $company): SolarCompanySetting
    {
        $setting = SolarCompanySetting::query()
            ->with('marginRanges')
            ->firstOrNew([
                'company_id' => $company->id,
            ]);

        if (! $setting->relationLoaded('marginRanges')) {
            $setting->setRelation('marginRanges', collect());
        }

        if ($setting->margin_mode === null || $setting->margin_mode === '') {
            $setting->margin_mode = SolarCompanySetting::MARGIN_MODE_FIXED;
        }

        return $setting;
    }

    /**
     * @return array{
     *     attributes: array<string, mixed>,
     *     margin_ranges: array<int, array<string, mixed>>
     * }
     */
    private function validatedPayload(Request $request): array
    {
        $data = $request->validate([
            'default_module_power' => ['nullable', 'integer', 'min:1'],
            'price_per_kwp' => ['nullable', 'numeric', 'min:0'],
            'margin_mode' => ['required', 'in:' . SolarCompanySetting::MARGIN_MODE_FIXED . ',' . SolarCompanySetting::MARGIN_MODE_RANGE],
            'margin_percent' => ['nullable', 'numeric', 'min:0'],
            'default_inverter_model' => ['nullable', 'string', 'max:255'],
            'margin_ranges' => ['nullable', 'array'],
            'margin_ranges.*.min_kwp' => ['nullable', 'numeric', 'min:0'],
            'margin_ranges.*.max_kwp' => ['nullable', 'numeric', 'min:0'],
            'margin_ranges.*.margin_percent' => ['nullable', 'numeric', 'min:0'],
            'margin_ranges.*.requires_negotiation' => ['nullable', 'boolean'],
        ]);

        $data['default_inverter_model'] = isset($data['default_inverter_model'])
            ? trim((string) $data['default_inverter_model']) ?: null
            : null;

        $marginRanges = $this->normalizeMarginRanges($data['margin_ranges'] ?? []);

        if (($data['margin_mode'] ?? SolarCompanySetting::MARGIN_MODE_FIXED) === SolarCompanySetting::MARGIN_MODE_RANGE) {
            $this->assertValidMarginRanges($marginRanges);
        } else {
            $marginRanges = [];
        }

        return [
            'attributes' => [
                'default_module_power' => $data['default_module_power'] ?? null,
                'price_per_kwp' => $data['price_per_kwp'] ?? null,
                'margin_mode' => $data['margin_mode'],
                'margin_percent' => $data['margin_percent'] ?? null,
                'default_inverter_model' => $data['default_inverter_model'],
            ],
            'margin_ranges' => $marginRanges,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $ranges
     * @return array<int, array<string, mixed>>
     */
    private function normalizeMarginRanges(array $ranges): array
    {
        $normalized = [];

        foreach ($ranges as $range) {
            $minKwp = $this->nullableFloat($range['min_kwp'] ?? null);
            $maxKwp = $this->nullableFloat($range['max_kwp'] ?? null);
            $marginPercent = $this->nullableFloat($range['margin_percent'] ?? null);
            $requiresNegotiation = (bool) ($range['requires_negotiation'] ?? false);

            if ($minKwp === null && $maxKwp === null && $marginPercent === null && ! $requiresNegotiation) {
                continue;
            }

            $normalized[] = [
                'min_kwp' => $minKwp,
                'max_kwp' => $maxKwp,
                'margin_percent' => $marginPercent,
                'requires_negotiation' => $requiresNegotiation,
            ];
        }

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $ranges
     */
    private function assertValidMarginRanges(array $ranges): void
    {
        $errors = [];

        if ($ranges === []) {
            throw ValidationException::withMessages([
                'margin_ranges' => 'Cadastre pelo menos uma faixa para usar o modo por faixa.',
            ]);
        }

        foreach ($ranges as $index => $range) {
            $position = $index + 1;
            $minKwp = $range['min_kwp'];
            $maxKwp = $range['max_kwp'];
            $marginPercent = $range['margin_percent'];
            $requiresNegotiation = (bool) $range['requires_negotiation'];

            if ($minKwp === null) {
                $errors["margin_ranges.$index.min_kwp"] = "Informe o kWp inicial da faixa {$position}.";
            }

            if ($maxKwp !== null && $minKwp !== null && $maxKwp <= $minKwp) {
                $errors["margin_ranges.$index.max_kwp"] = "O kWp final da faixa {$position} precisa ser maior que o inicial.";
            }

            if (! $requiresNegotiation && $marginPercent === null) {
                $errors["margin_ranges.$index.margin_percent"] = "Informe a margem da faixa {$position} ou marque negociacao obrigatoria.";
            }
        }

        $sortedRanges = array_map(
            static fn (array $range, int $index): array => $range + ['input_index' => $index],
            $ranges,
            array_keys($ranges),
        );

        usort($sortedRanges, static function (array $left, array $right): int {
            $minComparison = ($left['min_kwp'] ?? 0) <=> ($right['min_kwp'] ?? 0);

            if ($minComparison !== 0) {
                return $minComparison;
            }

            $leftMax = $left['max_kwp'] ?? INF;
            $rightMax = $right['max_kwp'] ?? INF;

            return $leftMax <=> $rightMax;
        });

        foreach ($sortedRanges as $index => $range) {
            $originalIndex = (int) $range['input_index'];
            $minKwp = $range['min_kwp'];
            $maxKwp = $range['max_kwp'];

            if ($maxKwp === null) {
                if ($index !== array_key_last($sortedRanges)) {
                    $errors["margin_ranges.$originalIndex.max_kwp"] = 'A faixa sem limite final precisa ser a ultima da lista.';
                }
            }

            if ($index === 0) {
                continue;
            }

            $previous = $sortedRanges[$index - 1];
            $previousMax = $previous['max_kwp'];

            if ($previousMax === null || $minKwp === null) {
                continue;
            }

            if ($minKwp <= $previousMax) {
                $errors["margin_ranges.$originalIndex.min_kwp"] = 'Essa faixa sobrepoe a faixa anterior.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 2);
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
