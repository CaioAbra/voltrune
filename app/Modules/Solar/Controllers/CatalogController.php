<?php

namespace App\Modules\Solar\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Solar\Models\SolarCatalogItem;
use App\Modules\Solar\Services\SolarNavigationService;
use App\Support\CurrentCompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        private readonly SolarNavigationService $navigation,
    ) {
    }

    public function index(Request $request): View
    {
        $company = $this->resolveCurrentCompany($request);
        $filters = [
            'q' => trim((string) $request->input('q')),
            'type' => (string) $request->input('type', ''),
            'category' => (string) $request->input('category', ''),
            'status' => (string) $request->input('status', ''),
            'sort' => (string) $request->input('sort', 'recent'),
        ];
        $allowedTypes = ['material', 'service'];
        $allowedCategories = array_keys($this->categoryOptions());
        $allowedStatuses = ['active', 'inactive'];
        $allowedSorts = ['recent', 'name_asc', 'cost_desc', 'price_desc'];

        if (! in_array($filters['type'], $allowedTypes, true)) {
            $filters['type'] = '';
        }

        if (! in_array($filters['category'], $allowedCategories, true)) {
            $filters['category'] = '';
        }

        if (! in_array($filters['status'], $allowedStatuses, true)) {
            $filters['status'] = '';
        }

        if (! in_array($filters['sort'], $allowedSorts, true)) {
            $filters['sort'] = 'recent';
        }

        $baseQuery = SolarCatalogItem::query()
            ->where('company_id', $company->id);

        $summaryItems = (clone $baseQuery)->get();
        $itemsQuery = clone $baseQuery;

        if ($filters['q'] !== '') {
            $search = '%' . $filters['q'] . '%';
            $itemsQuery->where(function ($query) use ($search): void {
                $query->where('name', 'like', $search)
                    ->orWhere('brand', 'like', $search)
                    ->orWhere('sku', 'like', $search)
                    ->orWhere('supplier', 'like', $search);
            });
        }

        if ($filters['type'] !== '') {
            $itemsQuery->where('type', $filters['type']);
        }

        if ($filters['category'] !== '') {
            $itemsQuery->where('category', $filters['category']);
        }

        if ($filters['status'] === 'active') {
            $itemsQuery->where('is_active', true);
        } elseif ($filters['status'] === 'inactive') {
            $itemsQuery->where('is_active', false);
        }

        match ($filters['sort']) {
            'name_asc' => $itemsQuery->orderBy('name'),
            'cost_desc' => $itemsQuery->orderByDesc('default_cost')->orderBy('name'),
            'price_desc' => $itemsQuery->orderByDesc('default_price')->orderBy('name'),
            default => $itemsQuery->latest(),
        };

        $items = $itemsQuery->get();
        $hasActiveFilters = $filters['q'] !== ''
            || $filters['type'] !== ''
            || $filters['category'] !== ''
            || $filters['status'] !== ''
            || $filters['sort'] !== 'recent';

        return view('solar.catalog.index', [
            'pageTitle' => 'Catalogo operacional',
            'pageDescription' => 'Gerencie equipamentos, servicos e custos base usados pela equipe comercial.',
            'navigationItems' => $this->navigation->items(),
            'items' => $items,
            'filters' => $filters,
            'hasActiveFilters' => $hasActiveFilters,
            'typeOptions' => $this->typeOptions(),
            'categoryOptions' => $this->categoryOptions(),
            'sortOptions' => [
                'recent' => 'Mais recentes',
                'name_asc' => 'Nome (A-Z)',
                'cost_desc' => 'Maior custo base',
                'price_desc' => 'Maior preco base',
            ],
            'summary' => [
                'total' => $summaryItems->count(),
                'active' => $summaryItems->where('is_active', true)->count(),
                'materials' => $summaryItems->where('type', 'material')->count(),
                'services' => $summaryItems->where('type', 'service')->count(),
                'filtered' => $items->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $data = $this->validatedData($request);
        $data['company_id'] = $company->id;

        SolarCatalogItem::query()->create($data);

        return redirect()
            ->route('solar.catalog.index')
            ->with('solar_status', 'Item do catalogo criado com sucesso.');
    }

    public function update(Request $request, int $item): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $itemRecord = $this->resolveItem($company, $item);
        $itemRecord->update($this->validatedData($request));

        return redirect()
            ->route('solar.catalog.index')
            ->with('solar_status', 'Item do catalogo atualizado com sucesso.');
    }

    public function destroy(Request $request, int $item): RedirectResponse
    {
        $company = $this->resolveCurrentCompany($request);
        $itemRecord = $this->resolveItem($company, $item);
        $itemRecord->delete();

        return redirect()
            ->route('solar.catalog.index')
            ->with('solar_status', 'Item do catalogo removido com sucesso.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'type' => ['required', 'in:material,service'],
            'category' => ['required', 'in:' . implode(',', array_keys($this->categoryOptions()))],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'unit_label' => ['nullable', 'string', 'max:30'],
            'default_quantity' => ['required', 'numeric', 'min:0.01'],
            'default_cost' => ['required', 'numeric', 'min:0'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $data['unit_label'] = trim((string) ($data['unit_label'] ?? '')) !== ''
            ? trim((string) $data['unit_label'])
            : 'un';

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function typeOptions(): array
    {
        return [
            'material' => 'Material',
            'service' => 'Servico',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function categoryOptions(): array
    {
        return [
            'module' => 'Modulo',
            'inverter' => 'Inversor',
            'structure' => 'Estrutura',
            'installation' => 'Instalacao',
            'cabling' => 'Cabeamento',
            'approval' => 'Homologacao',
            'electrical_design' => 'Projeto eletrico',
            'art' => 'ART',
            'other' => 'Outro',
        ];
    }

    private function resolveCurrentCompany(Request $request): Company
    {
        $user = $request->user();

        abort_unless($user, 403);

        $company = CurrentCompanyContext::resolve($user, $request->session());

        abort_unless($company instanceof Company, 403, 'Empresa ativa nao encontrada.');

        return $company;
    }

    private function resolveItem(Company $company, int $itemId): SolarCatalogItem
    {
        return SolarCatalogItem::query()
            ->where('company_id', $company->id)
            ->findOrFail($itemId);
    }
}
