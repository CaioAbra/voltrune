@extends('solar.layout')

@section('title', 'Projetos | Voltrune Solar')

@section('solar-content')
    <section class="hub-card">
        <div class="hub-actions">
            <a href="{{ route('solar.projects.create') }}" class="hub-btn">Novo projeto</a>
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

        @if ($projects->isEmpty())
            <h2>Projetos de instalacao</h2>
            <p>Nenhum projeto cadastrado para esta empresa ainda.</p>
            <p class="hub-note">Cada projeto representa o local da instalacao solar e servira de base para localizacao tecnica e simulacao futura.</p>
        @else
            <div class="hub-card hub-card--subtle solar-table-panel">
                <h2>Local da instalacao</h2>
                <p>Projetos representam o local da instalacao solar. O sistema parte do CEP e dos dados basicos do imovel para preparar a geolocalizacao interna.</p>
            </div>

            <div class="hub-table-wrap solar-table-wrap">
                <table class="hub-table solar-table solar-table--projects">
                    <thead>
                        <tr>
                            <th>Projeto</th>
                            <th>Cliente</th>
                            <th>Cidade/UF</th>
                            <th>Consumo mensal</th>
                            <th>Concessionaria</th>
                            <th>Status</th>
                            <th>Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr class="solar-table__row">
                                <td data-label="Projeto" class="solar-table__cell solar-table__cell--primary">
                                    <strong class="solar-table__entity">{{ $project->name }}</strong>
                                    <div class="hub-table__sub solar-table__meta">
                                        {{ $project->street ?: 'Endereco em preparacao' }}{{ $project->number ? ', '.$project->number : '' }}
                                        @if ($project->zip_code)
                                            <span> | CEP {{ $project->zip_code }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td data-label="Cliente" class="solar-table__cell">{{ $project->customer?->name ?: '-' }}</td>
                                <td data-label="Cidade / UF" class="solar-table__cell">
                                    @php
                                        $location = trim(collect([$project->city, $project->state])->filter()->implode('/'));
                                    @endphp
                                    {{ $location !== '' ? $location : 'Aguardando localizacao' }}
                                </td>
                                <td data-label="Consumo mensal" class="solar-table__cell">{{ $project->monthly_consumption_kwh ? number_format((float) $project->monthly_consumption_kwh, 2, ',', '.') . ' kWh' : '-' }}</td>
                                <td data-label="Concessionaria" class="solar-table__cell">{{ $project->utility_company ?: '-' }}</td>
                                <td data-label="Status" class="solar-table__cell">
                                    <div class="hub-inline-badges">
                                        <span class="hub-badge">{{ strtoupper($project->status) }}</span>
                                        <span class="hub-badge hub-badge--muted">{{ strtoupper($project->geocoding_status ?? 'pending') }}</span>
                                    </div>
                                </td>
                                <td data-label="Acoes" class="solar-table__cell solar-table__cell--actions">
                                    <div class="hub-table-actions solar-table__actions">
                                        <a href="{{ route('solar.projects.show', $project->id) }}" class="hub-btn">Ver</a>
                                        <a href="{{ route('solar.projects.edit', $project->id) }}" class="hub-btn hub-btn--subtle">Editar</a>
                                        <form action="{{ route('solar.projects.destroy', $project->id) }}" method="post">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hub-btn" onclick="return confirm('Excluir este projeto?');">Excluir</button>
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
