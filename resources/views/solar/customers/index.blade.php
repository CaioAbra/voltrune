@extends('solar.layout')

@section('title', 'Clientes | Solar')

@section('solar-content')
    <section class="hub-card">
        <div class="hub-actions">
            <a href="{{ route('solar.customers.create') }}" class="hub-btn">Novo cliente</a>
        </div>

        @if (session('solar_status'))
            <div
                class="hub-alert hub-alert--success solar-flash-alert"
                data-flash-alert
                data-flash-timeout="5000"
                role="status"
                aria-live="polite"
            >
                <div class="solar-flash-alert__content">
                    {{ session('solar_status') }}
                </div>
                <button type="button" class="solar-flash-alert__close" data-flash-close aria-label="Fechar aviso">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if ($customers->isEmpty())
            <h2>Clientes</h2>
            <p>Nenhum cliente cadastrado para esta empresa ainda.</p>
            <p class="hub-note">Os registros criados no Solar ficam isolados por company_id.</p>
        @else
            <div class="hub-table-wrap">
                <table class="hub-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Contato</th>
                            <th>Documento</th>
                            <th>Cidade/UF</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            <tr>
                                <td>
                                    <strong>{{ $customer->name }}</strong>
                                    <div class="hub-table__sub">
                                        Criado em {{ $customer->created_at?->format('d/m/Y H:i') ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $customer->email ?: '-' }}</div>
                                    <div class="hub-table__sub">{{ $customer->phone ?: 'Sem telefone' }}</div>
                                </td>
                                <td>{{ $customer->document ?: '-' }}</td>
                                <td>
                                    @php
                                        $location = trim(collect([$customer->city, $customer->state])->filter()->implode('/'));
                                    @endphp
                                    {{ $location !== '' ? $location : '-' }}
                                </td>
                                <td>
                                    <div class="hub-table-actions">
                                        <a href="{{ route('solar.customers.edit', $customer->id) }}" class="hub-btn hub-btn--subtle">
                                            Editar
                                        </a>
                                        <form action="{{ route('solar.customers.destroy', $customer->id) }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hub-btn" onclick="return confirm('Excluir este cliente?');">
                                                Excluir
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
