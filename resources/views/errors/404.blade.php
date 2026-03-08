@extends('errors.layout')

@section('title', '404 | Página não encontrada')
@section('heading', '404 — Caminho não encontrado')
@section('message', 'Não encontramos esta rota. Use o mapa abaixo para retomar o caminho e voltar para a área certa.')

@section('outer_eggs')
    <div class="error-eggs" aria-label="Easter eggs do mapa 404">
        <div class="error-egg error-egg--north" data-error-egg-shy>
            <button type="button" class="error-egg__trigger" data-error-egg-trigger data-error-egg-target="egg_manifesto" aria-expanded="false">
                Runa
            </button>
            <div id="egg_manifesto" class="error-egg__panel" hidden>
                Missão Voltrune: transformar operação em clareza, confiança e evolução contínua.
            </div>
        </div>

        <div class="error-egg error-egg--west">
            <button type="button" class="error-egg__trigger" data-error-egg-trigger data-error-egg-target="egg_ops" aria-expanded="false">
                Ops
            </button>
            <div id="egg_ops" class="error-egg__panel" hidden>
                Backoffice forte: menos planilha, mais controle real da carteira de clientes.
            </div>
        </div>

        <div class="error-egg error-egg--east">
            <button type="button" class="error-egg__trigger" data-error-egg-trigger data-error-egg-target="egg_hub" aria-expanded="false">
                Hub
            </button>
            <div id="egg_hub" class="error-egg__panel" hidden>
                Cada rota perdida vira aprendizado de produto.
            </div>
        </div>
    </div>
@endsection

@section('illustration')
    <div class="error-map" aria-hidden="true">
        <svg viewBox="0 0 720 280" role="img">
            <rect class="error-map__frame" x="8" y="8" width="704" height="264" rx="14"></rect>

            <g class="error-map__texture">
                <path d="M36 62h648M36 112h648M36 162h648M36 212h648"></path>
                <path d="M76 32v216M196 32v216M316 32v216M436 32v216M556 32v216M676 32v216"></path>
            </g>

            <path class="error-map__line" d="M60 210 C140 178, 168 92, 250 112 C330 132, 356 220, 440 204 C520 188, 562 102, 660 126"></path>
            <path class="error-map__line error-map__line--alt" d="M58 106 C142 74, 198 170, 282 150 C356 132, 392 58, 482 86 C560 108, 602 194, 670 170"></path>
            <path class="error-map__line error-map__line--ghost" d="M126 236 C202 194, 286 236, 352 198 C418 160, 498 178, 612 140"></path>

            <circle class="error-map__node" cx="60" cy="210" r="5"></circle>
            <circle class="error-map__node error-map__node--b" cx="250" cy="112" r="5"></circle>
            <circle class="error-map__node error-map__node--c" cx="440" cy="204" r="5"></circle>
            <circle class="error-map__current" cx="660" cy="126" r="7"></circle>

            <g class="error-map__poi">
                <path class="error-map__poi-icon" d="M115 84l8-12 8 12-8 12-8-12zm8-7l3 5-3 5-3-5 3-5z"></path>
                <text class="error-map__poi-label" x="137" y="88">Solar Keep</text>
            </g>

            <g class="error-map__poi error-map__poi--alt">
                <path class="error-map__poi-icon" d="M334 70h20l-3 14h-14l-3-14zm5 3l1 8m4-8l1 8m4-8l1 8"></path>
                <text class="error-map__poi-label" x="360" y="82">Vigilante Outpost</text>
            </g>

            <g class="error-map__poi error-map__poi--alt2">
                <path class="error-map__poi-icon" d="M544 206c5-6 7-10 7-15 0-5-3-8-7-8s-7 3-7 8c0 5 2 9 7 15zm0-11v8"></path>
                <text class="error-map__poi-label" x="566" y="194">Agro Fields</text>
            </g>

            <g class="error-map__egg error-map__egg--ufo">
                <ellipse cx="598" cy="60" rx="10" ry="4"></ellipse>
                <path d="M588 60c3 6 17 6 20 0"></path>
            </g>
            <g class="error-map__egg error-map__egg--d20">
                <path d="M206 226l8-10 12 4 0 12-12 4-8-10z"></path>
            </g>
            <text class="error-map__egg-label" x="44" y="42">Quest log: Clareza • Confiança • Evolução</text>
        </svg>
    </div>
@endsection

@section('primary_action')
    <a href="{{ route('home') }}" class="error-btn">Voltar ao site</a>
@endsection

@section('secondary_action')
    <a href="{{ route('hub.login') }}" class="error-btn error-btn--ghost">Ir para o Hub</a>
@endsection
