<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-intro">
            <p class="footer-brand">Voltrune</p>
            <p>Estrategia, design e execucao tecnica para marcas que precisam vender com mais consistencia.</p>
        </div>

        <div class="footer-column">
            <p class="footer-title">Navegacao</p>
            <a href="{{ route('servicos') }}">Servicos</a>
            <a href="{{ route('portfolio') }}">Missoes</a>
            <a href="{{ route('sistemas') }}">Sistemas</a>
            <a href="{{ route('contato') }}">Contato</a>
        </div>

        <div class="footer-column">
            <p class="footer-title">Proximos passos</p>
            <a href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Falar com a Voltrune</a>
            <a href="{{ route('portal') }}">Ver hospedagem</a>
        </div>
    </div>

    <div class="container footer-meta">
        <small>(c) {{ now()->year }} Voltrune. Todos os direitos reservados.</small>
    </div>
</footer>
