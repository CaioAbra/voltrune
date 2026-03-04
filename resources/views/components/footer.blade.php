<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-intro">
            <p class="footer-brand">Voltrune</p>
            <p>Estratégia, design e execução técnica para marcas que precisam vender com mais consistência.</p>
        </div>

        <div class="footer-column">
            <p class="footer-title">Navegação</p>
            <a href="{{ route('servicos') }}">Serviços</a>
            <a href="{{ route('portfolio') }}">Portfólio</a>
            <a href="{{ route('sistemas') }}">Sistemas</a>
            <a href="{{ route('contato') }}">Contato</a>
        </div>

        <div class="footer-column">
            <p class="footer-title">Próximos passos</p>
            <a href="{{ env('WHATSAPP_URL', 'https://wa.me/5511998479359') }}" target="_blank" rel="noopener">Falar com a Voltrune</a>
            <a href="{{ route('portal') }}">Ver hospedagem</a>
        </div>
    </div>

    <div class="container footer-meta">
        <small>
            <span>(c) {{ now()->year }} Voltrune.</span>
            <span>Todos os direitos reservados.</span>
        </small>
    </div>
</footer>
