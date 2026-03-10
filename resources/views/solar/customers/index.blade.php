@extends('solar.layout')

@section('title', 'Clientes | Solar')

@section('solar-content')
    <section class="hub-card">
        <div class="hub-actions">
            <a href="{{ route('solar.customers.create') }}" class="hub-btn">Novo cliente</a>
            <a href="{{ route('solar.projects.create') }}" class="hub-btn hub-btn--subtle">Novo projeto solar</a>
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
            <h2>Clientes contratantes</h2>
            <p>Nenhum cliente comercial cadastrado para esta empresa ainda.</p>
            <p class="hub-note">Aqui ficam as pessoas ou empresas contratantes. O local da instalacao sera tratado em Projetos.</p>
        @else
            <div class="hub-card hub-card--subtle solar-table-panel">
                <h2>Cadastro comercial</h2>
                <p>Clientes representam a pessoa ou empresa contratante. Cada cliente pode ter um ou mais projetos de instalacao solar.</p>
            </div>

            <div class="hub-table-wrap solar-table-wrap">
                <table class="hub-table solar-table solar-table--customers">
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
                            <tr class="solar-table__row">
                                <td data-label="Nome" class="solar-table__cell solar-table__cell--primary">
                                    <strong class="solar-table__entity">{{ $customer->name }}</strong>
                                    <div class="hub-table__sub solar-table__meta">
                                        Criado em {{ $customer->created_at?->format('d/m/Y H:i') ?? '-' }}
                                    </div>
                                </td>
                                <td data-label="Contato" class="solar-table__cell">
                                    <div>{{ $customer->email ?: '-' }}</div>
                                    <div class="hub-table__sub solar-table__meta">{{ $customer->phone ?: 'Sem telefone' }}</div>
                                </td>
                                <td data-label="Documento" class="solar-table__cell">{{ $customer->document ?: '-' }}</td>
                                <td data-label="Cidade / UF" class="solar-table__cell">
                                    @php
                                        $location = trim(collect([$customer->city, $customer->state])->filter()->implode('/'));
                                    @endphp
                                    {{ $location !== '' ? $location : '-' }}
                                </td>
                                <td data-label="Acoes" class="solar-table__cell solar-table__cell--actions">
                                    <div class="hub-table-actions solar-table__actions">
                                        <a href="{{ route('solar.projects.create', ['customer' => $customer->id]) }}" class="hub-btn">
                                            Criar projeto
                                        </a>
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
