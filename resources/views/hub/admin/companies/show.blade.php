@extends('hub.admin.layout')

@section('title', 'Admin | Operação da Empresa')

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
    @endphp

    <h1>Painel operacional do cliente</h1>

    @if ($errors->any())
        <div class="hub-alert hub-alert--danger">{{ $errors->first() }}</div>
    @endif

    @if (session('status'))
        <div class="hub-alert hub-alert--success">{{ session('status') }}</div>
    @endif

    <div class="hub-card">
        <h2>BLOCO 1 - Dados do cliente</h2>
        <p><strong>Empresa:</strong> {{ $company->name }}</p>
        <p><strong>Slug:</strong> {{ $company->slug }}</p>
        <p><strong>Status da conta:</strong> <span class="hub-badge">{{ strtoupper($company->status) }}</span></p>
        <p><strong>Responsável principal:</strong> {{ $owner?->name ?? 'Não definido' }} ({{ $owner?->email ?? '-' }})</p>

        <div class="hub-table-wrap">
            <table class="hub-table">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>E-mail</th>
                        <th>Role</th>
                        <th>Owner</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($visibleUsers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->pivot->role ?? '-' }}</td>
                            <td>{{ $user->pivot->is_owner ? 'Sim' : 'Não' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Nenhum usuário de cliente para exibir.</td>
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
                    <button
                        type="button"
                        class="custom-select__trigger"
                        data-custom-select-trigger
                        aria-haspopup="listbox"
                        aria-expanded="false"
                    >
                        <span class="custom-select__value" data-custom-select-value>{{ strtoupper($company->status) }}</span>
                        <span class="custom-select__icon" aria-hidden="true"></span>
                    </button>
                    <div class="custom-select__panel" data-custom-select-panel hidden></div>
                </div>
            </div>
            <button type="submit" class="hub-btn">Salvar status</button>
        </form>
    </div>

    <div class="hub-card">
        <h2>BLOCO 2 - Contratação</h2>
        @foreach ($productLabels as $key => $label)
            @php $contract = $contractsByProduct[$key] ?? null; @endphp
            <div class="hub-card">
                <h3>{{ $label }}</h3>
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
                            <button
                                type="button"
                                class="custom-select__trigger"
                                data-custom-select-trigger
                                aria-haspopup="listbox"
                                aria-expanded="false"
                            >
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
            </div>
        @endforeach
    </div>

    <div class="hub-card">
        <h2>BLOCO 3 - Cobrança manual</h2>
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
                    <button
                        type="button"
                        class="custom-select__trigger"
                        data-custom-select-trigger
                        aria-haspopup="listbox"
                        aria-expanded="false"
                    >
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
                    <button
                        type="button"
                        class="custom-select__trigger"
                        data-custom-select-trigger
                        aria-haspopup="listbox"
                        aria-expanded="false"
                    >
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
    </div>

    <div class="hub-card">
        <h2>BLOCO 4 - Liberação de acesso</h2>
        @foreach ($productLabels as $key => $label)
            @php $access = $accessByProduct[$key] ?? null; @endphp
            <form method="post" action="{{ route('hub.admin.companies.access.upsert', ['company' => $company, 'productKey' => $key]) }}" class="hub-auth-form">
                @csrf
                @method('PATCH')
                <div>
                    <label class="hub-auth-label" for="access_{{ $key }}">{{ $label }}</label>
                    <div class="custom-select" data-custom-select>
                        <select id="access_{{ $key }}" class="hub-auth-input" name="access_status" data-custom-select-native>
                            <option value="inactive" @selected(($access?->access_status ?? 'inactive') === 'inactive')>INATIVO</option>
                            <option value="active" @selected(($access?->access_status ?? '') === 'active')>ATIVO</option>
                        </select>
                        <button
                            type="button"
                            class="custom-select__trigger"
                            data-custom-select-trigger
                            aria-haspopup="listbox"
                            aria-expanded="false"
                        >
                            <span class="custom-select__value" data-custom-select-value>{{ strtoupper($access?->access_status ?? 'inactive') }}</span>
                            <span class="custom-select__icon" aria-hidden="true"></span>
                        </button>
                        <div class="custom-select__panel" data-custom-select-panel hidden></div>
                    </div>
                </div>
                <button type="submit" class="hub-btn">Salvar acesso {{ $label }}</button>
            </form>
        @endforeach
    </div>

    <div class="hub-actions">
        <a href="{{ route('hub.admin.companies.index') }}" class="hub-btn">Voltar para clientes</a>
    </div>
@endsection
