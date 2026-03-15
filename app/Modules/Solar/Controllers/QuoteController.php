<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\SolarQuote;
use App\Modules\Solar\Models\SolarQuoteItem;
use App\Modules\Solar\Models\SolarSimulation;
use App\Modules\Solar\Services\SolarNavigationService;
use App\Modules\Solar\Services\SolarQuoteBlueprintService;
use App\Support\CurrentCompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
        private readonly SolarQuoteBlueprintService $quoteBlueprints,
    ) {
    }

    public function index(Request $request): View
    {
        $company = $this->resolveCurrentCompany($request);
        $quotes = SolarQuote::query()
            ->with(['project.customer', 'simulation', 'items'])
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
        $simulationSnapshot = $this->quoteBlueprints->buildSimulationSnapshot($simulationRecord);
        $baseTotals = $this->quoteBlueprints->resolveBaseTotals($simulationSnapshot, $simulationRecord);

        $quote = SolarQuote::create([
            'company_id' => $company->id,
            'solar_project_id' => $simulationRecord->solar_project_id,
            'solar_simulation_id' => $simulationRecord->id,
            'simulation_snapshot_json' => $simulationSnapshot,
            'title' => $this->makeQuoteTitle($simulationRecord),
            'final_price' => $baseTotals['final_price'],
            'total_value' => $baseTotals['total_value'],
            'estimated_savings' => $baseTotals['estimated_savings'],
            'payback_months' => $baseTotals['payback_months'],
            'status' => 'draft',
            'notes' => $this->makeQuoteNotes($simulationRecord),
        ]);

        foreach ($this->quoteBlueprints->buildSeedItemsFromSimulation($simulationRecord) as $itemPayload) {
            $quote->items()->create($itemPayload);
        }

        $this->syncQuoteTotals($quote->refresh()->load('items', 'simulation'));

        return redirect()
            ->route('solar.quotes.edit', $quote->id)
            ->with('solar_status', 'Proposta criada a partir da simulacao ' . $simulationRecord->name . '.');
    }

    public function edit(Request $request, int $quote): View
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = SolarQuote::query()
            ->with(['project.customer', 'simulation', 'items'])
            ->where('company_id', $company->id)
            ->findOrFail($quote);

        return view('solar.quotes.edit', [
            'pageTitle' => 'Editar orcamento',
            'pageDescription' => 'Refine a proposta comercial gerada a partir da simulacao.',
            'navigationItems' => $this->navigation->items(),
            'quote' => $quoteRecord,
            'simulation' => $quoteRecord->simulation,
            'project' => $quoteRecord->project,
            'quoteSummary' => $this->quoteSummary($quoteRecord),
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

        $quoteRecord->loadMissing('items');
        $hasItems = $quoteRecord->items->isNotEmpty();
        $finalPrice = $hasItems
            ? $this->quoteSummary($quoteRecord)['total_price']
            : ($data['final_price'] ?? null);

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

    public function duplicate(Request $request, int $quote): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = $this->resolveQuote($company, $quote);
        $duplicate = $quoteRecord->replicate([
            'created_at',
            'updated_at',
        ]);
        $duplicate->title = $this->duplicateQuoteTitle($quoteRecord->title);
        $duplicate->status = 'draft';
        $duplicate->save();

        foreach ($quoteRecord->items as $item) {
            $newItem = $item->replicate([
                'created_at',
                'updated_at',
            ]);
            $newItem->solar_quote_id = $duplicate->id;
            $newItem->save();
        }

        $this->syncQuoteTotals($duplicate->refresh()->load('items'));

        return redirect()
            ->route('solar.quotes.edit', $duplicate->id)
            ->with('solar_status', 'Orcamento duplicado com sucesso.');
    }

    public function updateStatus(Request $request, int $quote): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = $this->resolveQuote($company, $quote);
        $data = $request->validate([
            'status' => ['required', 'in:draft,review,sent,approved,won,lost'],
        ]);

        $quoteRecord->update([
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('solar.quotes.edit', $quoteRecord->id)
            ->with('solar_status', 'Status da proposta atualizado para ' . $this->statusLabel($data['status']) . '.');
    }

    public function storeItem(Request $request, int $quote): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = $this->resolveQuote($company, $quote);
        $data = $request->validate([
            'type' => ['required', 'in:material,service'],
            'category' => ['required', 'in:module,inverter,structure,installation,cabling,approval,electrical_design,art,other'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $quantity = (float) $data['quantity'];
        $unitCost = (float) $data['unit_cost'];
        $unitPrice = (float) $data['unit_price'];

        $quoteRecord->items()->create([
            'type' => $data['type'],
            'category' => $data['category'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'unit_price' => $unitPrice,
            'total_cost' => round($quantity * $unitCost, 2),
            'total_price' => round($quantity * $unitPrice, 2),
        ]);

        $this->syncQuoteTotals($quoteRecord);

        return redirect()
            ->route('solar.quotes.edit', $quoteRecord->id)
            ->with('solar_status', 'Item adicionado ao orcamento com sucesso.');
    }

    public function destroyItem(Request $request, int $quote, int $item): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = $this->resolveQuote($company, $quote);
        $itemRecord = $quoteRecord->items()
            ->whereKey($item)
            ->firstOrFail();

        $itemRecord->delete();
        $this->syncQuoteTotals($quoteRecord);

        return redirect()
            ->route('solar.quotes.edit', $quoteRecord->id)
            ->with('solar_status', 'Item removido do orcamento com sucesso.');
    }

    private function resolveCurrentCompany(Request $request): Company
    {
        $user = $request->user();

        abort_unless($user, 403);

        $company = CurrentCompanyContext::resolve($user, $request->session());

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

    private function resolveQuote(Company $company, int $quoteId): SolarQuote
    {
        return SolarQuote::query()
            ->with(['project.customer', 'simulation', 'items'])
            ->where('company_id', $company->id)
            ->findOrFail($quoteId);
    }

    /**
     * @return array{total_cost: float, total_price: float, gross_profit: float, margin_percent: float, item_count: int}
     */
    private function quoteSummary(SolarQuote $quote): array
    {
        $quote->loadMissing('items');

        return [
            'total_cost' => round($quote->itemsTotalCost(), 2),
            'total_price' => round($quote->itemsTotalPrice(), 2),
            'gross_profit' => round($quote->itemsGrossProfit(), 2),
            'margin_percent' => round($quote->itemsMarginPercent(), 2),
            'item_count' => $quote->items->count(),
        ];
    }

    private function syncQuoteTotals(SolarQuote $quote): void
    {
        $summary = $this->quoteSummary($quote);

        if ($summary['item_count'] === 0) {
            $quote->loadMissing('simulation');
            $baseTotals = $this->quoteBlueprints->resolveBaseTotals(
                is_array($quote->simulation_snapshot_json) ? $quote->simulation_snapshot_json : null,
                $quote->simulation,
            );

            $quote->update($baseTotals);

            return;
        }

        $quote->update([
            'final_price' => $summary['total_price'],
            'total_value' => $summary['total_price'],
        ]);
    }

    private function duplicateQuoteTitle(string $title): string
    {
        $trimmedTitle = trim($title);

        if ($trimmedTitle === '') {
            return 'Proposta solar - copia';
        }

        return str_contains(mb_strtolower($trimmedTitle), 'copia')
            ? $trimmedTitle . ' 2'
            : $trimmedTitle . ' - copia';
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Rascunho',
            'review' => 'Em analise',
            'sent' => 'Enviado',
            'approved' => 'Aprovado',
            'won' => 'Fechado',
            'lost' => 'Perdido',
            default => strtoupper($status),
        };
    }
}
