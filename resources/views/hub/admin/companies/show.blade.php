@extends('hub.admin.layout')

@section('title', 'Painel Interno Voltrune | Operação da empresa')

@section('content')
    @php
        $adminEmails = \App\Support\HubAdminAccess::allowedEmails();
        $visibleUsers = $company->users->reject(fn ($user) => $adminEmails->contains(strtolower($user->email)));
        $owner = $visibleUsers->firstWhere('pivot.is_owner', true) ?? $visibleUsers->first();
        $financialStatusLabels = [
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'overdue' => 'Em atraso',
            'canceled' => 'Cancelado',
        ];
        $companyStatusLabels = [
            'pending' => 'Pendente',
            'active' => 'Ativa',
            'suspended' => 'Suspensa',
        ];
        $financialStatus = $latestBilling?->financial_status ?? 'pending';
        $activeAccessCount = collect($accessByProduct)->filter(fn ($access) => ($access?->access_status ?? 'inactive') === 'active')->count();
        $contractedCount = collect($contractsByProduct)->filter(fn ($contract) => $contract !== null)->count();
        $hasActiveAccess = $activeAccessCount > 0;
    @endphp

    <h1>Centro operacional do cliente</h1>
    <p>Gestão de dados cadastrais, contratação, cobrança manual e liberacao de acessos.</p>

    <section class="hub-card hub-admin-status-strip">
        <article>
            <p class="hub-kpi-card__label">Status da empresa</p>
            <p>@include('hub.admin.partials.status-badge', ['type' => 'company', 'value' => $company->status, 'label' => $companyStatusLabels[$company->status] ?? strtoupper($company->status)])</p>
        </article>
        <article>
            <p class="hub-kpi-card__label">Status financeiro</p>
            <p>@include('hub.admin.partials.status-badge', ['type' => 'financial', 'value' => $financialStatus, 'label' => $financialStatusLabels[$financialStatus] ?? strtoupper($financialStatus)])</p>
        </article>
        <article>
            <p class="hub-kpi-card__label">Contratações registradas</p>
            <p class="hub-status-number">{{ $contractedCount }}</p>
        </article>
        <article>
            <p class="hub-kpi-card__label">Acessos ativos</p>
            <p class="hub-status-number">{{ $activeAccessCount }}</p>
        </article>
    </section>

    @if ($company->status === 'pending')
        <div class="hub-alert hub-alert--warning">
            Cliente com conta pendente. Revisar onboarding e ativação para liberar operação normal.
        </div>
    @endif

    @if ($company->status === 'suspended')
        <div class="hub-alert hub-alert--danger">
            Conta suspensa. Priorizar regularização antes de novas liberações.
        </div>
    @endif

    @if ($financialStatus === 'overdue')
        <div class="hub-alert hub-alert--danger">
            Cobrança em atraso. Priorizar atualização do bloco de cobrança manual.
        </div>
    @endif

    @if (! $hasActiveAccess)
        <div class="hub-alert hub-alert--warning">
            Nenhum acesso ativo no momento. Validar contratação e liberar pelo menos um sistema.
        </div>
    @endif

    @if ($errors->any())
        <div class="hub-alert hub-alert--danger">{{ $errors->first() }}</div>
    @endif

    @if (session('status'))
        <div class="hub-alert hub-alert--success">{{ session('status') }}</div>
    @endif

    <section class="hub-card hub-admin-block">
        <h2>Dados do cliente</h2>

        <div class="hub-admin-summary-grid">
            <article class="hub-card hub-card--subtle">
                <h3>Empresa</h3>
                <p><strong>{{ $company->name }}</strong></p>
                <p class="hub-note">Slug: {{ $company->slug }}</p>
            </article>

            <article class="hub-card hub-card--subtle">
                <h3>Responsável principal</h3>
                <p><strong>{{ $owner?->name ?? 'Não definido' }}</strong></p>
                <p class="hub-note">{{ $owner?->email ?? '-' }}</p>
            </article>

            <article class="hub-card hub-card--subtle">
                <h3>Resumo operacional</h3>
                <p class="hub-note">Usuários vinculados: {{ $visibleUsers->count() }}</p>
                <p class="hub-note">Produtos ativos: {{ $activeAccessCount }}</p>
            </article>
        </div>

        <div class="hub-table-wrap">
            <table class="hub-table hub-table--responsive">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>E-mail</th>
                        <th>Função</th>
                        <th>Responsável</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visibleUsers as $user)
                        <tr>
                            <td data-label="Usuario">{{ $user->name }}</td>
                            <td data-label="E-mail">{{ $user->email }}</td>
                            <td data-label="Funcao">{{ $user->pivot->role ?? '-' }}</td>
                            <td>{{ $user->pivot->is_owner ? 'Sim' : 'Não' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Nenhum usuario de cliente para exibir.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <form method="post" action="{{ route('hub.admin.companies.status.update', $company) }}" class="hub-auth-form">
            @csrf
            @method('PATCH')
            <div>
                <label for="status" class="hub-auth-label">Alterar status da conta</label>
                <div class="custom-select" data-custom-select>
                    <select id="status" name="status" class="hub-auth-input" data-custom-select-native>
                        @foreach ($allowedStatuses as $status)
                            <option value="{{ $status }}" @selected($company->status === $status)>{{ strtoupper($status) }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="custom-select__trigger" data-custom-select-trigger aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select__value" data-custom-select-value>{{ strtoupper($company->status) }}</span>
                        <span class="custom-select__icon" aria-hidden="true"></span>
                    </button>
                    <div class="custom-select__panel" data-custom-select-panel hidden></div>
                </div>
            </div>
            <button type="submit" class="hub-btn">Salvar status</button>
        </form>
    </section>

    <section class="hub-card hub-admin-block">
        <h2>Contratação</h2>
        <div class="hub-admin-product-grid">
            @foreach ($productLabels as $key => $label)
                @php $contract = $contractsByProduct[$key] ?? null; @endphp
                <article class="hub-card hub-card--subtle">
                    <h3>{{ $label }}</h3>
                    @if (! $contract)
                        <p class="hub-table__sub">Sem contratação registrada.</p>
                    @endif
                    <form method="post" action="{{ route('hub.admin.companies.contracts.upsert', ['company' => $company, 'productKey' => $key]) }}" class="hub-auth-form">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="hub-auth-label" for="plan_name_{{ $key }}">Plano</label>
                            <input id="plan_name_{{ $key }}" class="hub-auth-input" type="text" name="plan_name" value="{{ $contract?->plan_name }}">
                        </div>
                        <div>
                            <label class="hub-auth-label" for="billing_cycle_{{ $key }}">Ciclo de cobrança</label>
                            <div class="custom-select" data-custom-select>
                                <select id="billing_cycle_{{ $key }}" name="billing_cycle" class="hub-auth-input" data-custom-select-native>
                                    <option value="">Não definido</option>
                                    @foreach ($allowedBillingCycles as $cycle)
                                        <option value="{{ $cycle }}" @selected(($contract?->billing_cycle ?? '') === $cycle)>{{ strtoupper($cycle) }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="custom-select__trigger" data-custom-select-trigger aria-haspopup="listbox" aria-expanded="false">
                                    <span class="custom-select__value" data-custom-select-value>{{ strtoupper($contract?->billing_cycle ?? 'Não definido') }}</span>
                                    <span class="custom-select__icon" aria-hidden="true"></span>
                                </button>
                                <div class="custom-select__panel" data-custom-select-panel hidden></div>
                            </div>
                        </div>
                        <div>
                            <label class="hub-auth-label" for="negotiated_value_{{ $key }}">Valor negociado</label>
                            <input id="negotiated_value_{{ $key }}" class="hub-auth-input" type="number" name="negotiated_value" min="0" step="0.01" value="{{ $contract?->negotiated_value }}">
                        </div>
                        <div>
                            <label class="hub-auth-label" for="commercial_notes_{{ $key }}">Observações comerciais</label>
                            <textarea id="commercial_notes_{{ $key }}" class="hub-auth-input" name="commercial_notes" rows="3">{{ $contract?->commercial_notes }}</textarea>
                        </div>
                        <button type="submit" class="hub-btn">Salvar contratação {{ $label }}</button>
                    </form>
                </article>
            @endforeach
        </div>
    </section>

    <section class="hub-card hub-admin-block">
        <h2>Cobrança manual</h2>
        <p class="hub-note">Status atual: @include('hub.admin.partials.status-badge', ['type' => 'financial', 'value' => $financialStatus, 'label' => $financialStatusLabels[$financialStatus] ?? strtoupper($financialStatus)])</p>

        <form method="post" action="{{ route('hub.admin.companies.billing.store', $company) }}" class="hub-auth-form">
            @csrf
            <div>
                <label class="hub-auth-label" for="financial_status">Status financeiro</label>
                <div class="custom-select" data-custom-select>
                    <select id="financial_status" name="financial_status" class="hub-auth-input" data-custom-select-native>
                        @foreach ($allowedFinancialStatuses as $status)
                            <option value="{{ $status }}" @selected(($latestBilling?->financial_status ?? 'pending') === $status)>{{ $financialStatusLabels[$status] ?? strtoupper($status) }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="custom-select__trigger" data-custom-select-trigger aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select__value" data-custom-select-value>{{ $financialStatusLabels[$latestBilling?->financial_status ?? 'pending'] ?? strtoupper($latestBilling?->financial_status ?? 'pending') }}</span>
                        <span class="custom-select__icon" aria-hidden="true"></span>
                    </button>
                    <div class="custom-select__panel" data-custom-select-panel hidden></div>
                </div>
            </div>
            <div>
                <label class="hub-auth-label" for="payment_method">Forma de pagamento</label>
                <div class="custom-select" data-custom-select>
                    <select id="payment_method" name="payment_method" class="hub-auth-input" data-custom-select-native>
                        <option value="">Não definido</option>
                        @foreach ($allowedPaymentMethods as $method)
                            <option value="{{ $method }}" @selected(($latestBilling?->payment_method ?? '') === $method)>{{ strtoupper($method) }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="custom-select__trigger" data-custom-select-trigger aria-haspopup="listbox" aria-expanded="false">
                        <span class="custom-select__value" data-custom-select-value>{{ strtoupper($latestBilling?->payment_method ?? 'Não definido') }}</span>
                        <span class="custom-select__icon" aria-hidden="true"></span>
                    </button>
                    <div class="custom-select__panel" data-custom-select-panel hidden></div>
                </div>
            </div>
            <div>
                <label class="hub-auth-label" for="last_payment_date">Data do último pagamento</label>
                <input id="last_payment_date" class="hub-auth-input" type="date" name="last_payment_date" value="{{ optional($latestBilling?->last_payment_date)->format('Y-m-d') }}">
            </div>
            <div>
                <label class="hub-auth-label" for="paid_amount">Valor pago</label>
                <input id="paid_amount" class="hub-auth-input" type="number" name="paid_amount" min="0" step="0.01" value="{{ $latestBilling?->paid_amount }}">
            </div>
            <div>
                <label class="hub-auth-label" for="next_billing_date">Próxima cobrança</label>
                <input id="next_billing_date" class="hub-auth-input" type="date" name="next_billing_date" value="{{ optional($latestBilling?->next_billing_date)->format('Y-m-d') }}">
            </div>
            <div>
                <label class="hub-auth-label" for="financial_notes">Observações financeiras</label>
                <textarea id="financial_notes" class="hub-auth-input" name="financial_notes" rows="3">{{ $latestBilling?->financial_notes }}</textarea>
            </div>
            <button type="submit" class="hub-btn">Registrar cobrança</button>
        </form>
    </section>

    <section class="hub-card hub-admin-block">
        <h2>Liberação de acessos</h2>
        <div class="hub-admin-access-grid">
            @foreach ($productLabels as $key => $label)
                @php $access = $accessByProduct[$key] ?? null; @endphp
                <article class="hub-card hub-card--subtle">
                    <h3>{{ $label }}</h3>
                    <p>@include('hub.admin.partials.status-badge', ['type' => 'access', 'value' => $access?->access_status ?? 'inactive', 'label' => strtoupper($access?->access_status ?? 'inactive')])</p>
                    <form method="post" action="{{ route('hub.admin.companies.access.upsert', ['company' => $company, 'productKey' => $key]) }}" class="hub-auth-form">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="hub-auth-label" for="access_{{ $key }}">Status de acesso</label>
                            <div class="custom-select" data-custom-select>
                                <select id="access_{{ $key }}" class="hub-auth-input" name="access_status" data-custom-select-native>
                                    <option value="inactive" @selected(($access?->access_status ?? 'inactive') === 'inactive')>INATIVO</option>
                                    <option value="active" @selected(($access?->access_status ?? '') === 'active')>ATIVO</option>
                                </select>
                                <button type="button" class="custom-select__trigger" data-custom-select-trigger aria-haspopup="listbox" aria-expanded="false">
                                    <span class="custom-select__value" data-custom-select-value>{{ strtoupper($access?->access_status ?? 'inactive') }}</span>
                                    <span class="custom-select__icon" aria-hidden="true"></span>
                                </button>
                                <div class="custom-select__panel" data-custom-select-panel hidden></div>
                            </div>
                        </div>
                        <button type="submit" class="hub-btn">Salvar acesso {{ $label }}</button>
                    </form>
                </article>
            @endforeach
        </div>
    </section>

    <div class="hub-actions">
        <a href="{{ route('hub.admin.companies.index') }}" class="hub-btn">Voltar para clientes</a>
    </div>
@endsection
