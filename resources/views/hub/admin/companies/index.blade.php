@extends('hub.admin.layout')

@section('title', 'Painel Interno Voltrune | Operação de clientes')

@section('content')
    @php
        $focus = $focus ?? 'clients';
        $adminEmails = \App\Support\HubAdminAccess::allowedEmails();
        $financialStatusLabels = [
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'overdue' => 'Em atraso',
            'canceled' => 'Cancelado',
        ];
        $titles = [
            'clients' => 'Operação de clientes',
            'contracts' => 'Contratações',
            'billing' => 'Cobrança manual',
            'access' => 'Liberação de acessos',
        ];
        $descriptions = [
            'clients' => 'Visão operacional da carteira com status de conta, financeiro e produtos liberados.',
            'contracts' => 'Acompanhe produto contratado, plano, ciclo e valor negociado por cliente.',
            'billing' => 'Monitore situação financeira, forma de pagamento e próximas cobranças.',
            'access' => 'Controle quais plataformas estão ativas ou inativas para cada cliente.',
        ];
        $routeByFocus = [
            'clients' => 'hub.admin.companies.index',
            'contracts' => 'hub.admin.contracts.index',
            'billing' => 'hub.admin.billing.index',
            'access' => 'hub.admin.access.index',
        ];
    @endphp

    <h1>{{ $titles[$focus] ?? 'Operação de clientes' }}</h1>
    <p>{{ $descriptions[$focus] ?? 'Carteira operacional da Voltrune.' }}</p>

    @if (session('status'))
        <div class="hub-alert hub-alert--success">{{ session('status') }}</div>
    @endif

    <section class="hub-card">
        <h2>Filtros operacionais</h2>
        <form method="get" action="{{ route($routeByFocus[$focus] ?? 'hub.admin.companies.index') }}" class="hub-filter-grid">
            <div>
                <label for="company_status" class="hub-auth-label">Status da conta</label>
                <div class="custom-select" data-custom-select>
                    <select id="company_status" name="company_status" class="hub-auth-input" data-custom-select-native>
                        <option value="">Todos</option>
                        @foreach ($allowedStatuses as $status)
                            <option value="{{ $status }}" @selected(($filters['company_status'] ?? '') === $status)>{{ strtoupper($status) }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="custom-select__trigger" data-custom-select-trigger aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select__value" data-custom-select-value>Todos</span>
                        <span class="custom-select__icon" aria-hidden="true"></span>
                    </button>
                    <div class="custom-select__panel" data-custom-select-panel hidden></div>
                </div>
            </div>

            <div>
                <label for="financial_status" class="hub-auth-label">Status financeiro</label>
                <div class="custom-select" data-custom-select>
                    <select id="financial_status" name="financial_status" class="hub-auth-input" data-custom-select-native>
                        <option value="">Todos</option>
                        @foreach ($allowedFinancialStatuses as $status)
                            <option value="{{ $status }}" @selected(($filters['financial_status'] ?? '') === $status)>{{ $financialStatusLabels[$status] ?? strtoupper($status) }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="custom-select__trigger" data-custom-select-trigger aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select__value" data-custom-select-value>Todos</span>
                        <span class="custom-select__icon" aria-hidden="true"></span>
                    </button>
                    <div class="custom-select__panel" data-custom-select-panel hidden></div>
                </div>
            </div>

            <div>
                <label for="product_key" class="hub-auth-label">Produto liberado</label>
                <div class="custom-select" data-custom-select>
                    <select id="product_key" name="product_key" class="hub-auth-input" data-custom-select-native>
                        <option value="">Todos</option>
                        @foreach ($productLabels as $key => $label)
                            <option value="{{ $key }}" @selected(($filters['product_key'] ?? '') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="custom-select__trigger" data-custom-select-trigger aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select__value" data-custom-select-value>Todos</span>
                        <span class="custom-select__icon" aria-hidden="true"></span>
                    </button>
                    <div class="custom-select__panel" data-custom-select-panel hidden></div>
                </div>
            </div>

            @if ($focus === 'access')
                <input type="hidden" name="access_state" value="{{ $filters['access_state'] ?? '' }}">
            @endif

            <div class="hub-filter-actions">
                <button type="submit" class="hub-btn">Filtrar</button>
                <a href="{{ route($routeByFocus[$focus] ?? 'hub.admin.companies.index') }}" class="hub-btn">Limpar</a>
            </div>
        </form>
    </section>

    <section class="hub-card">
        <h2>Carteira de clientes</h2>

        <div class="hub-table-wrap">
            <table class="hub-table hub-table--operational">
                <thead>
                    @if ($focus === 'contracts')
                        <tr>
                            <th>Empresa</th>
                            <th>Responsável</th>
                            <th>Produtos contratados</th>
                            <th>Ciclo predominante</th>
                            <th>Valor negociado (total)</th>
                            <th>Ações</th>
                        </tr>
                    @elseif ($focus === 'billing')
                        <tr>
                            <th>Empresa</th>
                            <th>Status financeiro</th>
                            <th>Forma de pagamento</th>
                            <th>Último pagamento</th>
                            <th>Próxima cobrança</th>
                            <th>Ações</th>
                        </tr>
                    @elseif ($focus === 'access')
                        <tr>
                            <th>Empresa</th>
                            <th>Status da conta</th>
                            <th>Produtos ativos</th>
                            <th>Produtos inativos</th>
                            <th>Responsável</th>
                            <th>Ações</th>
                        </tr>
                    @else
                        <tr>
                            <th>Empresa</th>
                            <th>Responsável principal</th>
                            <th>E-mail</th>
                            <th>Status da conta</th>
                            <th>Status financeiro</th>
                            <th>Produtos liberados</th>
                            <th>Data de cadastro</th>
                            <th>Ações</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    @forelse ($companies as $company)
                        @php
                            $visibleUsers = $company->users->reject(fn ($user) => $adminEmails->contains(strtolower($user->email)));
                            $owner = $visibleUsers->firstWhere('pivot.is_owner', true) ?? $visibleUsers->first();
                            $billing = $company->billingRecords->first();
                            $contracts = $company->contracts ?? collect();
                            $productAccesses = $company->productAccesses ?? collect();
                            $activeProducts = $productAccesses->where('access_status', 'active')->pluck('product_key')->all();
                            $inactiveProducts = $productAccesses->where('access_status', 'inactive')->pluck('product_key')->all();
                            $activeProductLabels = collect($activeProducts)->map(fn ($key) => $productLabels[$key] ?? strtoupper($key));
                            $inactiveProductLabels = collect($inactiveProducts)->map(fn ($key) => $productLabels[$key] ?? strtoupper($key));
                            $billingCycle = $contracts->pluck('billing_cycle')->filter()->countBy()->sortDesc()->keys()->first();
                            $totalNegotiated = $contracts->sum(fn ($contract) => (float) ($contract->negotiated_value ?? 0));
                        @endphp

                        @if ($focus === 'contracts')
                            <tr>
                                <td>
                                    <strong>{{ $company->name }}</strong>
                                    <div class="hub-table__sub">{{ $company->slug }}</div>
                                </td>
                                <td>{{ $owner?->name ?? 'Não definido' }}</td>
                                <td>{{ $contracts->count() > 0 ? $contracts->pluck('product_key')->map(fn ($key) => strtoupper($key))->implode(', ') : 'Nenhum' }}</td>
                                <td>{{ $billingCycle ? strtoupper($billingCycle) : 'Não definido' }}</td>
                                <td>R$ {{ number_format($totalNegotiated, 2, ',', '.') }}</td>
                                <td class="hub-table-actions">
                                    <a href="{{ route('hub.admin.companies.show', $company) }}" class="hub-btn">Operar</a>
                                </td>
                            </tr>
                        @elseif ($focus === 'billing')
                            <tr>
                                <td>
                                    <strong>{{ $company->name }}</strong>
                                    <div class="hub-table__sub">{{ $company->slug }}</div>
                                </td>
                                <td>@include('hub.admin.partials.status-badge', ['type' => 'financial', 'value' => $billing?->financial_status ?? 'pending', 'label' => $financialStatusLabels[$billing?->financial_status ?? 'pending'] ?? strtoupper($billing?->financial_status ?? 'pending')])</td>
                                <td>{{ $billing?->payment_method ? strtoupper($billing->payment_method) : 'Não definido' }}</td>
                                <td>{{ optional($billing?->last_payment_date)->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ optional($billing?->next_billing_date)->format('d/m/Y') ?? '-' }}</td>
                                <td class="hub-table-actions">
                                    <a href="{{ route('hub.admin.companies.show', $company) }}" class="hub-btn">Operar</a>
                                </td>
                            </tr>
                        @elseif ($focus === 'access')
                            <tr>
                                <td>
                                    <strong>{{ $company->name }}</strong>
                                    <div class="hub-table__sub">{{ $company->slug }}</div>
                                </td>
                                <td>@include('hub.admin.partials.status-badge', ['type' => 'company', 'value' => $company->status])</td>
                                <td>
                                    @if ($activeProductLabels->isEmpty())
                                        <span class="hub-table__sub">Nenhum</span>
                                    @else
                                        <div class="hub-inline-badges">
                                            @foreach ($activeProductLabels as $label)
                                                @include('hub.admin.partials.status-badge', ['type' => 'access', 'value' => 'active', 'label' => $label])
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($inactiveProductLabels->isEmpty())
                                        <span class="hub-table__sub">Nenhum</span>
                                    @else
                                        <div class="hub-inline-badges">
                                            @foreach ($inactiveProductLabels as $label)
                                                @include('hub.admin.partials.status-badge', ['type' => 'access', 'value' => 'inactive', 'label' => $label])
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $owner?->name ?? 'Não definido' }}</td>
                                <td class="hub-table-actions">
                                    <a href="{{ route('hub.admin.companies.show', $company) }}" class="hub-btn">Operar</a>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td>
                                    <strong>{{ $company->name }}</strong>
                                    <div class="hub-table__sub">{{ $company->slug }}</div>
                                </td>
                                <td>{{ $owner?->name ?? 'Não definido' }}</td>
                                <td>{{ $owner?->email ?? '-' }}</td>
                                <td>@include('hub.admin.partials.status-badge', ['type' => 'company', 'value' => $company->status])</td>
                                <td>@include('hub.admin.partials.status-badge', ['type' => 'financial', 'value' => $billing?->financial_status ?? 'pending', 'label' => $financialStatusLabels[$billing?->financial_status ?? 'pending'] ?? strtoupper($billing?->financial_status ?? 'pending')])</td>
                                <td>
                                    @if ($activeProductLabels->isEmpty())
                                        <span class="hub-table__sub">Nenhum</span>
                                    @else
                                        <div class="hub-inline-badges">
                                            @foreach ($activeProductLabels as $label)
                                                @include('hub.admin.partials.status-badge', ['type' => 'access', 'value' => 'active', 'label' => $label])
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>{{ optional($company->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="hub-table-actions">
                                    <a href="{{ route('hub.admin.companies.show', $company) }}" class="hub-btn">Operar</a>
                                    <form method="post" action="{{ route('hub.admin.companies.status.update', $company) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="active">
                                        <button type="submit" class="hub-btn" @disabled($company->status === 'active')>Ativar</button>
                                    </form>
                                    <form method="post" action="{{ route('hub.admin.companies.status.update', $company) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="suspended">
                                        <button type="submit" class="hub-btn" @disabled($company->status === 'suspended')>Suspender</button>
                                    </form>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="8">Nenhuma empresa encontrada para os filtros selecionados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
