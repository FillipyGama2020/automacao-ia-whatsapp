<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WhatsApp AI Panel — Atendimento no WhatsApp com Inteligência Artificial</title>
    <meta name="description" content="Atendimento automático no WhatsApp com IA para o seu negócio. Respostas 24h, captura de leads e relatórios — configurado pela Sua Empresa Agência.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .plano-card { display: flex; flex-direction: column; }
        .plano-card ul { flex-grow: 1; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">

    @php
        $whatsappLink = 'https://wa.me/5527992923778?text=' . urlencode('Olá! Quero saber mais sobre o WhatsApp AI Panel.');
    @endphp

    <header class="bg-white border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
            <img src="{{ asset('images/logo.png') }}" alt="WhatsApp AI Panel" class="h-10 object-contain">
            <div class="flex items-center gap-3">
                <a href="https://painel.example.com/login" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-md text-sm font-semibold hover:bg-gray-800">
                    Entrar
                </a>
            </div>
        </div>
    </header>

    <section class="bg-white">
        <div class="max-w-6xl mx-auto px-6 py-16 sm:py-24 text-center">
            <h1 class="text-3xl sm:text-5xl font-bold text-gray-900 leading-tight">
                Atendimento no WhatsApp,<br class="hidden sm:block"> respondido por Inteligência Artificial.
            </h1>
            <p class="mt-6 text-lg text-gray-500 max-w-2xl mx-auto">
                O WhatsApp AI Panel conecta um assistente de IA ao WhatsApp Business do seu negócio — responde clientes
                24 horas por dia, captura leads e organiza o histórico de conversas, sem você precisar contratar
                nem ficar online o tempo todo.
            </p>
            <div class="mt-10">
                <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="inline-flex items-center px-8 py-3 bg-emerald-600 text-white rounded-md text-base font-semibold hover:bg-emerald-700 shadow-sm">
                    Quero contratar
                </a>
                <p class="mt-3 text-sm text-gray-400">+55 27 99292-3778 · atendimento pela Sua Empresa Agência</p>
            </div>
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-6 py-16">
        <h2 class="text-2xl font-bold text-gray-900 text-center">Como funciona</h2>
        <div class="mt-10 grid sm:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">1</div>
                <h3 class="mt-4 font-semibold text-gray-900">Conectamos seu WhatsApp</h3>
                <p class="mt-2 text-sm text-gray-500">Você continua sendo o dono do número — conectamos oficialmente à API do WhatsApp Business, sem perder histórico.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">2</div>
                <h3 class="mt-4 font-semibold text-gray-900">Configuramos o assistente</h3>
                <p class="mt-2 text-sm text-gray-500">Personalidade, horários de atendimento, perguntas frequentes e regras do seu negócio, do jeito que você atende hoje.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg">3</div>
                <h3 class="mt-4 font-semibold text-gray-900">A IA atende por você</h3>
                <p class="mt-2 text-sm text-gray-500">Respostas automáticas 24h, captura de leads e um painel com todo o histórico de conversas e relatórios.</p>
            </div>
        </div>
    </section>

    <section class="bg-white border-y border-gray-100">
        <div class="max-w-6xl mx-auto px-6 py-16">
            <h2 class="text-2xl font-bold text-gray-900 text-center">Planos</h2>
            <p class="mt-2 text-center text-gray-500">Escolha o plano de acordo com o volume de atendimento do seu negócio.</p>

            <div class="mt-10 grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($planos as $plano)
                    <div class="plano-card border border-gray-200 rounded-xl p-6 shadow-sm">
                        <h3 class="text-lg font-bold text-gray-900">{{ $plano->nome }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ $plano->descricao }}</p>

                        <div class="mt-4">
                            <span class="text-2xl font-bold text-gray-900">R$ {{ number_format($plano->preco_mensal, 0, ',', '.') }}</span>
                            <span class="text-sm text-gray-400">/mês</span>
                        </div>
                        <p class="text-xs text-gray-400">
                            + implantação a partir de R$ {{ number_format($plano->taxa_implantacao, 0, ',', '.') }}
                        </p>

                        <ul class="mt-5 space-y-2 text-sm text-gray-600">
                            <li>
                                {{ $plano->conversasIlimitadas() ? 'Conversas ilimitadas' : 'Até ' . $plano->limite_conversas_mensais . ' conversas/mês' }}
                            </li>
                            <li>
                                {{ $plano->agentesIlimitados() ? 'Agentes de IA ilimitados' : $plano->limite_agentes . ' agente(s) de IA' }}
                            </li>
                            @foreach ($plano->recursos as $recurso)
                                <li>{{ $recurso->descricao }}</li>
                            @endforeach
                        </ul>

                        <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="mt-6 inline-flex justify-center items-center px-4 py-2 border border-gray-900 text-gray-900 rounded-md text-sm font-semibold hover:bg-gray-900 hover:text-white transition">
                            Contratar {{ $plano->nome }}
                        </a>
                    </div>
                @endforeach
            </div>

            <p class="mt-6 text-center text-xs text-gray-400">
                Valores de implantação do plano Enterprise são de partida, negociados por contrato conforme volume e complexidade.
            </p>
        </div>
    </section>

    <section class="max-w-6xl mx-auto px-6 py-16 text-center">
        <h2 class="text-2xl font-bold text-gray-900">Pronto para automatizar seu atendimento?</h2>
        <p class="mt-3 text-gray-500">Fale com a gente no WhatsApp e tire suas dúvidas antes de contratar.</p>
        <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="mt-6 inline-flex items-center px-8 py-3 bg-emerald-600 text-white rounded-md text-base font-semibold hover:bg-emerald-700 shadow-sm">
            Falar no WhatsApp — +55 27 99292-3778
        </a>
    </section>

    <footer class="bg-gray-900 text-gray-400">
        <div class="max-w-6xl mx-auto px-6 py-10 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm">&copy; {{ date('Y') }} Sua Empresa Web Comunicação LTDA — WhatsApp AI Panel</p>
            <div class="flex items-center gap-6 text-sm">
                <a href="https://painel.example.com/termos-de-uso" class="hover:text-white">Termos de Uso</a>
                <a href="https://painel.example.com/politica-de-privacidade" class="hover:text-white">Política de Privacidade</a>
                <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="hover:text-white">WhatsApp</a>
            </div>
        </div>
    </footer>

</body>
</html>
