<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\SolarQuote;
use App\Modules\Solar\Models\SolarSimulation;
use App\Modules\Solar\Services\SolarNavigationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
    ) {
    }

    public function index(Request $request): View
    {
        $company = $this->resolveCurrentCompany($request);
        $quotes = SolarQuote::query()
            ->with(['project.customer', 'simulation'])
            ->where('company_id', $company->id)
            ->latest()
            ->get();

        return view('solar.quotes.index', [
            'pageTitle' => 'Orcamentos',
            'pageDescription' => 'Ambiente do Solar para consolidar propostas, itens e fechamento comercial.',
            'navigationItems' => $this->navigation->items(),
            'quotes' => $quotes,
        ]);
    }

    public function storeFromSimulation(Request $request, int $simulation): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $simulationRecord = SolarSimulation::query()
            ->with(['project.customer'])
            ->where('company_id', $company->id)
            ->findOrFail($simulation);

        $quote = SolarQuote::create([
            'company_id' => $company->id,
            'solar_project_id' => $simulationRecord->solar_project_id,
            'solar_simulation_id' => $simulationRecord->id,
            'title' => $this->makeQuoteTitle($simulationRecord),
            'final_price' => $simulationRecord->suggested_price,
            'total_value' => $simulationRecord->suggested_price ?? 0,
            'estimated_savings' => $simulationRecord->estimated_monthly_savings,
            'payback_months' => $simulationRecord->estimated_payback_months,
            'status' => 'draft',
            'notes' => $this->makeQuoteNotes($simulationRecord),
        ]);

        return redirect()
            ->route('solar.quotes.edit', $quote->id)
            ->with('solar_status', 'Proposta criada a partir da simulacao ' . $simulationRecord->name . '.');
    }

    public function edit(Request $request, int $quote): View
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = SolarQuote::query()
            ->with(['project.customer', 'simulation'])
            ->where('company_id', $company->id)
            ->findOrFail($quote);

        return view('solar.quotes.edit', [
            'pageTitle' => 'Editar orcamento',
            'pageDescription' => 'Refine a proposta comercial gerada a partir da simulacao.',
            'navigationItems' => $this->navigation->items(),
            'quote' => $quoteRecord,
            'simulation' => $quoteRecord->simulation,
            'project' => $quoteRecord->project,
        ]);
    }

    public function update(Request $request, int $quote): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = SolarQuote::query()
            ->where('company_id', $company->id)
            ->findOrFail($quote);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
            'final_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $finalPrice = $data['final_price'] ?? null;

        $quoteRecord->update([
            'title' => $data['title'],
            'status' => $data['status'],
            'final_price' => $finalPrice,
            'total_value' => $finalPrice ?? $quoteRecord->total_value,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()
            ->route('solar.quotes.edit', $quoteRecord->id)
            ->with('solar_status', 'Proposta atualizada com sucesso.');
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

    private function makeQuoteTitle(SolarSimulation $simulation): string
    {
        $customerName = trim((string) $simulation->project?->customer?->name);

        if ($customerName !== '') {
            return 'Proposta solar - ' . $customerName . ' - ' . $simulation->name;
        }

        return 'Proposta solar - ' . $simulation->name;
    }

    private function makeQuoteNotes(SolarSimulation $simulation): string
    {
        $lines = array_filter([
            'Proposta gerada automaticamente a partir da simulacao ' . $simulation->name . '.',
            $simulation->system_power_kwp ? 'Potencia estimada: ' . number_format((float) $simulation->system_power_kwp, 2, ',', '.') . ' kWp.' : null,
            $simulation->estimated_generation_kwh ? 'Geracao estimada: ' . number_format((float) $simulation->estimated_generation_kwh, 2, ',', '.') . ' kWh/mes.' : null,
            $simulation->estimated_monthly_savings ? 'Economia mensal estimada: R$ ' . number_format((float) $simulation->estimated_monthly_savings, 2, ',', '.') . '.' : null,
            $simulation->notes ? 'Observacoes da simulacao: ' . $simulation->notes : null,
        ]);

        return implode("\n", $lines);
    }
}
