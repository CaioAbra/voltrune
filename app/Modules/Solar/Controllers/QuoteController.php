<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\SolarCatalogItem;
use App\Modules\Solar\Models\SolarQuote;
use App\Modules\Solar\Models\SolarQuoteItem;
use App\Modules\Solar\Models\SolarSimulation;
use App\Modules\Solar\Services\SolarNavigationService;
use App\Modules\Solar\Services\SolarQuoteBlueprintService;
use App\Modules\Solar\Services\SolarQuoteWorkflowService;
use App\Support\CurrentCompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
        private readonly SolarQuoteBlueprintService $quoteBlueprints,
        private readonly SolarQuoteWorkflowService $quoteWorkflow,
    ) {
    }

    public function index(Request $request): View
    {
        $company = $this->resolveCurrentCompany($request);
        $filters = [
            'q' => trim((string) $request->input('q')),
            'status' => (string) $request->input('status', ''),
            'composition' => (string) $request->input('composition', ''),
            'temperature' => (string) $request->input('temperature', ''),
            'sort' => (string) $request->input('sort', 'recent'),
        ];
        $allowedStatuses = ['draft', 'review', 'sent', 'approved', 'won', 'lost'];
        $allowedComposition = ['with_items', 'without_items'];
        $allowedTemperatures = ['cold', 'warm', 'hot'];
        $allowedSorts = ['recent', 'title_asc', 'price_desc', 'payback_asc', 'items_desc', 'follow_up_asc'];

        if (! in_array($filters['status'], $allowedStatuses, true)) {
            $filters['status'] = '';
        }

        if (! in_array($filters['composition'], $allowedComposition, true)) {
            $filters['composition'] = '';
        }

        if (! in_array($filters['temperature'], $allowedTemperatures, true)) {
            $filters['temperature'] = '';
        }

        if (! in_array($filters['sort'], $allowedSorts, true)) {
            $filters['sort'] = 'recent';
        }

        $baseQuery = SolarQuote::query()
            ->with(['project.customer', 'simulation', 'items.catalogItem'])
            ->withCount('items')
            ->where('company_id', $company->id);

        $summaryQuotes = (clone $baseQuery)->get();
        $quotesQuery = clone $baseQuery;

        if ($filters['q'] !== '') {
            $search = '%' . $filters['q'] . '%';

            $quotesQuery->where(function ($query) use ($search): void {
                $query->where('title', 'like', $search)
                    ->orWhere('proposal_code', 'like', $search)
                    ->orWhere('owner_name', 'like', $search)
                    ->orWhereHas('project', function ($projectQuery) use ($search): void {
                        $projectQuery->where('name', 'like', $search)
                            ->orWhereHas('customer', function ($customerQuery) use ($search): void {
                                $customerQuery->where('name', 'like', $search);
                            });
                    })
                    ->orWhereHas('simulation', function ($simulationQuery) use ($search): void {
                        $simulationQuery->where('name', 'like', $search);
                    });
            });
        }

        if ($filters['status'] !== '') {
            $quotesQuery->where('status', $filters['status']);
        }

        if ($filters['composition'] === 'with_items') {
            $quotesQuery->has('items');
        } elseif ($filters['composition'] === 'without_items') {
            $quotesQuery->doesntHave('items');
        }

        if ($filters['temperature'] !== '') {
            $quotesQuery->where('deal_temperature', $filters['temperature']);
        }

        match ($filters['sort']) {
            'title_asc' => $quotesQuery->orderBy('title'),
            'price_desc' => $quotesQuery->orderByDesc('final_price')->orderByDesc('id'),
            'payback_asc' => $quotesQuery->orderBy('payback_months')->orderByDesc('id'),
            'items_desc' => $quotesQuery->orderByDesc('items_count')->orderByDesc('id'),
            'follow_up_asc' => $quotesQuery->orderByRaw('CASE WHEN next_contact_at IS NULL THEN 1 ELSE 0 END')->orderBy('next_contact_at')->orderByDesc('id'),
            default => $quotesQuery->latest(),
        };

        $quotes = $quotesQuery->get();
        $dueFollowUps = $summaryQuotes->filter(fn (SolarQuote $quote): bool => $quote->next_contact_at !== null && $quote->next_contact_at->isPast())->count();
        $hasActiveFilters = $filters['q'] !== ''
            || $filters['status'] !== ''
            || $filters['composition'] !== ''
            || $filters['temperature'] !== ''
            || $filters['sort'] !== 'recent';

        return view('solar.quotes.index', [
            'pageTitle' => 'Orcamentos',
            'pageDescription' => 'Acompanhe orcamentos em montagem, enviados e fechados dentro do fluxo comercial.',
            'navigationItems' => $this->navigation->items(),
            'quotes' => $quotes,
            'filters' => $filters,
            'hasActiveFilters' => $hasActiveFilters,
            'summary' => [
                'total' => $summaryQuotes->count(),
                'draft' => $summaryQuotes->where('status', 'draft')->count(),
                'review' => $summaryQuotes->where('status', 'review')->count(),
                'sent' => $summaryQuotes->where('status', 'sent')->count(),
                'won' => $summaryQuotes->where('status', 'won')->count(),
                'lost' => $summaryQuotes->where('status', 'lost')->count(),
                'follow_up_due' => $dueFollowUps,
                'filtered' => $quotes->count(),
            ],
        ]);
    }

    public function storeFromSimulation(Request $request, int $simulation): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $simulationRecord = SolarSimulation::query()
            ->with(['project.customer'])
            ->where('company_id', $company->id)
            ->findOrFail($simulation);
        $catalogItems = SolarCatalogItem::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();
        $simulationSnapshot = $this->quoteBlueprints->buildSimulationSnapshot($simulationRecord);
        $baseTotals = $this->quoteBlueprints->resolveBaseTotals($simulationSnapshot, $simulationRecord);
        $versioning = $this->quoteWorkflow->initializeVersioning($company->id);
        $seedItems = $this->quoteBlueprints->buildSeedItemsFromSimulation($simulationRecord, $catalogItems);

        $quote = SolarQuote::create(array_merge([
            'company_id' => $company->id,
            'solar_project_id' => $simulationRecord->solar_project_id,
            'solar_simulation_id' => $simulationRecord->id,
            'simulation_snapshot_json' => $simulationSnapshot,
            'title' => $this->makeQuoteTitle($simulationRecord),
            'owner_name' => $request->user()?->name,
            'final_price' => $baseTotals['final_price'],
            'total_value' => $baseTotals['total_value'],
            'estimated_savings' => $baseTotals['estimated_savings'],
            'payback_months' => $baseTotals['payback_months'],
            'status' => 'draft',
            'deal_temperature' => 'warm',
            'notes' => $this->makeQuoteNotes($simulationRecord),
        ], $versioning));

        foreach ($seedItems as $itemPayload) {
            $quote->items()->create($itemPayload);
        }

        $quote = $quote->refresh()->load('items', 'simulation');
        $this->syncQuoteTotals($quote);
        $quote = $quote->refresh()->load('items', 'simulation');

        $this->recordEvent(
            $quote,
            'quote_created',
            'Versao inicial registrada',
            'Orcamento criado a partir da simulacao ' . $simulationRecord->name . '.',
            [
                'simulation_name' => $simulationRecord->name,
                'proposal_code' => $quote->proposal_code,
            ],
        );

        if ($seedItems !== []) {
            $this->recordEvent(
                $quote,
                'composition_seeded',
                'Composicao inicial carregada',
                'Os itens base foram trazidos automaticamente da simulacao para acelerar a proposta.',
                [
                    'items_seeded' => count($seedItems),
                ],
            );
        }

        return redirect()
            ->route('solar.quotes.edit', $quote->id)
            ->with('solar_status', 'Orcamento criado a partir da simulacao ' . $simulationRecord->name . '.');
    }

    public function edit(Request $request, int $quote): View
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = SolarQuote::query()
            ->with(['project.customer', 'simulation', 'items.catalogItem', 'events', 'sourceQuote'])
            ->where('company_id', $company->id)
            ->findOrFail($quote);
        $catalogItems = SolarCatalogItem::query()
            ->where('company_id', $company->id)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('solar.quotes.edit', [
            'pageTitle' => 'Editar orcamento',
            'pageDescription' => 'Refine o orcamento comercial gerado a partir da simulacao.',
            'navigationItems' => $this->navigation->items(),
            'quote' => $quoteRecord,
            'simulation' => $quoteRecord->simulation,
            'project' => $quoteRecord->project,
            'quoteSummary' => $this->quoteSummary($quoteRecord),
            'proposalVersions' => $this->proposalVersions($company, $quoteRecord),
            'catalogItems' => $catalogItems,
        ]);
    }

    public function proposal(Request $request, int $quote): View
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = SolarQuote::query()
            ->with(['project.customer', 'simulation', 'items.catalogItem', 'events', 'sourceQuote'])
            ->where('company_id', $company->id)
            ->findOrFail($quote);

        return view('solar.quotes.proposal', [
            'quote' => $quoteRecord,
            'project' => $quoteRecord->project,
            'simulation' => $quoteRecord->simulation,
            'quoteSummary' => $this->quoteSummary($quoteRecord),
            'proposalVersions' => $this->proposalVersions($company, $quoteRecord),
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
            'status' => ['required', 'in:draft,review,sent,approved,won,lost'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'next_contact_at' => ['nullable', 'date'],
            'closing_forecast_at' => ['nullable', 'date'],
            'deal_temperature' => ['required', 'in:cold,warm,hot'],
            'final_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $quoteRecord->loadMissing('items');
        $hasItems = $quoteRecord->items->isNotEmpty();
        $finalPrice = $hasItems
            ? $this->quoteSummary($quoteRecord)['total_price']
            : ($data['final_price'] ?? null);
        $original = [
            'title' => (string) $quoteRecord->title,
            'owner_name' => trim((string) ($quoteRecord->owner_name ?? '')),
            'status' => (string) $quoteRecord->status,
            'notes' => trim((string) ($quoteRecord->notes ?? '')),
            'final_price' => $quoteRecord->final_price !== null ? round((float) $quoteRecord->final_price, 2) : null,
            'next_contact_at' => $quoteRecord->next_contact_at?->format('Y-m-d\TH:i'),
            'closing_forecast_at' => $quoteRecord->closing_forecast_at?->format('Y-m-d\TH:i'),
            'deal_temperature' => (string) ($quoteRecord->deal_temperature ?? 'warm'),
        ];

        $quoteRecord->update(array_merge([
            'title' => $data['title'],
            'owner_name' => $data['owner_name'] ?? null,
            'status' => $data['status'],
            'final_price' => $finalPrice,
            'total_value' => $finalPrice ?? $quoteRecord->total_value,
            'next_contact_at' => $data['next_contact_at'] ?? null,
            'closing_forecast_at' => $data['closing_forecast_at'] ?? null,
            'deal_temperature' => $data['deal_temperature'],
            'notes' => $data['notes'] ?? null,
        ], $this->quoteWorkflow->syncStatusTimeline($quoteRecord, $data['status'])));

        $changedFields = $this->changedFields($original, [
            'title' => $data['title'],
            'owner_name' => trim((string) ($data['owner_name'] ?? '')),
            'status' => $data['status'],
            'notes' => trim((string) ($data['notes'] ?? '')),
            'final_price' => $finalPrice !== null ? round((float) $finalPrice, 2) : null,
            'next_contact_at' => ! empty($data['next_contact_at']) ? date('Y-m-d\TH:i', strtotime((string) $data['next_contact_at'])) : null,
            'closing_forecast_at' => ! empty($data['closing_forecast_at']) ? date('Y-m-d\TH:i', strtotime((string) $data['closing_forecast_at'])) : null,
            'deal_temperature' => $data['deal_temperature'],
        ]);

        if ($original['status'] !== $data['status']) {
            $this->recordEvent(
                $quoteRecord,
                'status_changed',
                'Status comercial atualizado',
                'O orcamento saiu de ' . $this->statusLabel($original['status']) . ' para ' . $this->statusLabel($data['status']) . '.',
                [
                    'from' => $original['status'],
                    'to' => $data['status'],
                ],
            );
        }

        if ($changedFields !== []) {
            $this->recordEvent(
                $quoteRecord,
                'quote_updated',
                'Ajustes comerciais salvos',
                'Campos revisados nesta versao: ' . implode(', ', $changedFields) . '.',
                [
                    'fields' => $changedFields,
                ],
            );
        }

        return redirect()
            ->route('solar.quotes.edit', $quoteRecord->id)
            ->with('solar_status', 'Orcamento atualizado com sucesso.');
    }

    public function duplicate(Request $request, int $quote): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = $this->resolveQuote($company, $quote);
        $versioning = $this->quoteWorkflow->initializeVersioning($company->id, $quoteRecord);
        $duplicate = $quoteRecord->replicate([
            'created_at',
            'updated_at',
            'proposal_code',
            'version_group_code',
            'version_number',
            'source_quote_id',
            'sent_at',
            'approved_at',
            'won_at',
            'lost_at',
        ]);
        $duplicate->title = $this->versionQuoteTitle($quoteRecord->title, $versioning['version_number']);
        $duplicate->status = 'draft';
        $duplicate->owner_name = $request->user()?->name ?: $quoteRecord->owner_name;
        $duplicate->proposal_code = $versioning['proposal_code'];
        $duplicate->version_group_code = $versioning['version_group_code'];
        $duplicate->version_number = $versioning['version_number'];
        $duplicate->source_quote_id = $versioning['source_quote_id'];
        $duplicate->sent_at = null;
        $duplicate->approved_at = null;
        $duplicate->won_at = null;
        $duplicate->lost_at = null;
        $duplicate->next_contact_at = null;
        $duplicate->save();

        foreach ($quoteRecord->items as $item) {
            $newItem = $item->replicate([
                'created_at',
                'updated_at',
            ]);
            $newItem->solar_quote_id = $duplicate->id;
            $newItem->save();
        }

        $duplicate = $duplicate->refresh()->load('items');
        $this->syncQuoteTotals($duplicate);
        $duplicate = $duplicate->refresh()->load('items', 'simulation');

        $this->recordEvent(
            $quoteRecord,
            'version_spawned',
            'Nova versao aberta',
            'A versao ' . $duplicate->proposal_code . ' foi criada a partir deste orcamento.',
            [
                'new_quote_id' => $duplicate->id,
                'new_proposal_code' => $duplicate->proposal_code,
            ],
        );
        $this->recordEvent(
            $duplicate,
            'version_created',
            'Nova versao criada',
            'Esta versao nasceu a partir do orcamento ' . $quoteRecord->proposal_code . '.',
            [
                'source_quote_id' => $quoteRecord->id,
                'source_proposal_code' => $quoteRecord->proposal_code,
            ],
        );

        return redirect()
            ->route('solar.quotes.edit', $duplicate->id)
            ->with('solar_status', 'Nova versao do orcamento criada com sucesso.');
    }

    public function updateStatus(Request $request, int $quote): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = $this->resolveQuote($company, $quote);
        $data = $request->validate([
            'status' => ['required', 'in:draft,review,sent,approved,won,lost'],
        ]);
        $previousStatus = (string) $quoteRecord->status;

        $quoteRecord->update(array_merge([
            'status' => $data['status'],
        ], $this->quoteWorkflow->syncStatusTimeline($quoteRecord, $data['status'])));

        if ($previousStatus !== $data['status']) {
            $this->recordEvent(
                $quoteRecord,
                'status_changed',
                'Status comercial atualizado',
                'O orcamento saiu de ' . $this->statusLabel($previousStatus) . ' para ' . $this->statusLabel($data['status']) . '.',
                [
                    'from' => $previousStatus,
                    'to' => $data['status'],
                ],
            );
        }

        return redirect()
            ->route('solar.quotes.edit', $quoteRecord->id)
            ->with('solar_status', 'Status do orcamento atualizado para ' . $this->statusLabel($data['status']) . '.');
    }

    public function storeItem(Request $request, int $quote): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $quoteRecord = $this->resolveQuote($company, $quote);
        $data = $request->validate([
            'catalog_item_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:material,service'],
            'category' => ['nullable', 'in:module,inverter,structure,installation,cabling,approval,electrical_design,art,other'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $quantity = (float) $data['quantity'];
        $catalogItem = isset($data['catalog_item_id']) && $data['catalog_item_id'] !== null
            ? $this->resolveCatalogItem($company, (int) $data['catalog_item_id'])
            : null;

        if ($catalogItem instanceof SolarCatalogItem) {
            $unitCost = round((float) $catalogItem->default_cost, 2);
            $unitPrice = round((float) $catalogItem->default_price, 2);
            $item = $quoteRecord->items()->create([
                'solar_catalog_item_id' => $catalogItem->id,
                'type' => $catalogItem->type,
                'category' => $catalogItem->category,
                'name' => $catalogItem->name,
                'description' => $catalogItem->notes,
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'unit_price' => $unitPrice,
                'total_cost' => round($quantity * $unitCost, 2),
                'total_price' => round($quantity * $unitPrice, 2),
            ]);
        } else {
            $request->validate([
                'type' => ['required', 'in:material,service'],
                'category' => ['required', 'in:module,inverter,structure,installation,cabling,approval,electrical_design,art,other'],
                'name' => ['required', 'string', 'max:255'],
                'unit_cost' => ['required', 'numeric', 'min:0'],
                'unit_price' => ['required', 'numeric', 'min:0'],
            ]);

            $unitCost = (float) ($data['unit_cost'] ?? 0);
            $unitPrice = (float) ($data['unit_price'] ?? 0);

            $item = $quoteRecord->items()->create([
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
        }

        $this->syncQuoteTotals($quoteRecord);
        $quoteRecord = $quoteRecord->refresh()->load('items');

        $this->recordEvent(
            $quoteRecord,
            'item_added',
            'Composicao atualizada',
            'O item ' . $item->name . ' entrou na versao comercial.',
            [
                'item_name' => $item->name,
                'category' => $item->category,
                'quantity' => $quantity,
            ],
        );

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
        $itemName = $itemRecord->name;

        $itemRecord->delete();
        $this->syncQuoteTotals($quoteRecord);
        $quoteRecord = $quoteRecord->refresh()->load('items');

        $this->recordEvent(
            $quoteRecord,
            'item_removed',
            'Item removido',
            'O item ' . $itemName . ' saiu da composicao desta versao.',
            [
                'item_name' => $itemName,
            ],
        );

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
            return 'Orcamento solar - ' . $customerName . ' - ' . $simulation->name;
        }

        return 'Orcamento solar - ' . $simulation->name;
    }

    private function makeQuoteNotes(SolarSimulation $simulation): string
    {
        $lines = array_filter([
            'Orcamento gerado automaticamente a partir da simulacao ' . $simulation->name . '.',
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
            ->with(['project.customer', 'simulation', 'items.catalogItem', 'events', 'sourceQuote'])
            ->where('company_id', $company->id)
            ->findOrFail($quoteId);
    }

    /**
     * @return array{total_cost: float, total_price: float, gross_profit: float, margin_percent: float, item_count: int}
     */
    private function quoteSummary(SolarQuote $quote): array
    {
        if ($quote->relationLoaded('items')) {
            $quote->unsetRelation('items');
        }

        $quote->load('items');

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

    private function versionQuoteTitle(string $title, int $versionNumber): string
    {
        $baseTitle = preg_replace('/\s+\|\s+V\d+$/i', '', trim($title)) ?: 'Orcamento solar';

        return $baseTitle . ' | V' . str_pad((string) $versionNumber, 2, '0', STR_PAD_LEFT);
    }

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     * @return list<string>
     */
    private function changedFields(array $before, array $after): array
    {
        $labels = [
            'title' => 'titulo',
            'owner_name' => 'responsavel',
            'status' => 'status',
            'notes' => 'observacoes',
            'final_price' => 'preco final',
            'next_contact_at' => 'proximo contato',
            'closing_forecast_at' => 'previsao de fechamento',
            'deal_temperature' => 'temperatura da oportunidade',
        ];
        $changed = [];

        foreach ($labels as $field => $label) {
            if (($before[$field] ?? null) !== ($after[$field] ?? null)) {
                $changed[] = $label;
            }
        }

        return $changed;
    }

    /**
     * @return Collection<int, SolarQuote>
     */
    private function proposalVersions(Company $company, SolarQuote $quote): Collection
    {
        $groupCode = trim((string) ($quote->version_group_code ?: $quote->proposal_code));

        if ($groupCode === '') {
            return collect([$quote]);
        }

        return SolarQuote::query()
            ->where('company_id', $company->id)
            ->where('version_group_code', $groupCode)
            ->orderBy('version_number')
            ->get();
    }

    private function resolveCatalogItem(Company $company, int $catalogItemId): SolarCatalogItem
    {
        return SolarCatalogItem::query()
            ->where('company_id', $company->id)
            ->findOrFail($catalogItemId);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function recordEvent(
        SolarQuote $quote,
        string $eventType,
        string $title,
        ?string $description = null,
        array $payload = [],
    ): void {
        $quote->events()->create([
            'company_id' => $quote->company_id,
            'event_type' => $eventType,
            'title' => $title,
            'description' => $description,
            'payload_json' => $payload !== [] ? $payload : null,
        ]);
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'draft' => 'Em montagem',
            'review' => 'Em revisao interna',
            'sent' => 'Enviado ao cliente',
            'approved' => 'Aprovado',
            'won' => 'Fechado',
            'lost' => 'Perdido',
            default => strtoupper($status),
        };
    }
}
