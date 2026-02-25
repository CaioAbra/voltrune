<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <p class="footer-brand">Voltrune</p>
            <p>Ordem de artesaos digitais focada em conversao, SEO e execucao objetiva.</p>
        </div>

        <div>
            <p class="footer-title">Navegacao</p>
            <a href="{{ route('servicos') }}">Servicos</a>
            <a href="{{ route('portfolio') }}">Missoes</a>
            <a href="{{ route('sistemas') }}">Sistemas</a>
            <a href="{{ route('contato') }}">Contato</a>
        </div>

        <div>
            <p class="footer-title">Acao</p>
            <a href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Falar no WhatsApp</a>
            <a href="{{ route('portal') }}">Contratar Hospedagem</a>
        </div>
    </div>

    <div class="container footer-meta">
        <small>(c) {{ now()->year }} Voltrune. Todos os direitos reservados.</small>
    </div>
</footer>
