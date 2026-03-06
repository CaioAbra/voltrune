@extends('hub.layout')

@section('title', 'Assinatura e Acesso | Voltrune Hub')

@section('content')
    <h1>Assinatura e acesso</h1>
    <p>Gerencie o plano ativo, usuários autorizados e segurança da sua conta.</p>

    <div class="hub-grid">
        <article class="hub-card">
            <h2>Status da assinatura</h2>
            <p>Plano mensal ativo. Renovação e histórico de cobrança disponíveis em breve.</p>
            <span class="hub-badge">Gestão em evolução</span>
        </article>

        <article class="hub-card">
            <h2>Usuários autorizados</h2>
            <p>Controle de permissão por equipe e perfil será liberado gradualmente.</p>
            <span class="hub-badge">Em breve</span>
        </article>

        <article class="hub-card">
            <h2>Segurança</h2>
            <p>Atualização de senha e autenticação adicional estarão disponíveis nesta área.</p>
            <span class="hub-badge">Em breve</span>
        </article>
    </div>
@endsection
