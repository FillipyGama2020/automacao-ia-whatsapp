<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.index') }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('WhatsApp Oficial — ') }}{{ $cliente->nome_empresa }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-300 text-red-800 text-sm rounded-md p-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700">Conectar via WhatsApp (Embedded Signup)</h3>
                        <p class="text-xs text-gray-400 mt-1">
                            Use quando o cliente já te deu acesso de parceiro/administrador no negócio dele —
                            você conecta em nome dele sem precisar preencher as credenciais manualmente abaixo.
                            Um cliente pode ter mais de um número conectado.
                        </p>
                    </div>

                    <button type="button" id="btn-conectar-whatsapp" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 whitespace-nowrap">
                        {{ $integracoes->isEmpty() ? 'Conectar via WhatsApp' : 'Conectar outro número' }}
                    </button>
                </div>

                <div id="whatsapp-alert" class="hidden text-sm rounded-md p-4 mt-4"></div>
            </div>

            @php
                $labels = ['conectado' => 'Conectado', 'erro' => 'Erro', 'nao_conectado' => 'Não conectado'];
                $colors = [
                    'conectado' => 'bg-green-100 text-green-800',
                    'erro' => 'bg-red-100 text-red-800',
                    'nao_conectado' => 'bg-gray-100 text-gray-800',
                ];
            @endphp

            @forelse ($integracoes as $integracao)
                <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-6" x-data="{ credenciaisAbertas: false }">

                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500">
                                {{ $integracao->phone_number_id ? 'Número '.$integracao->phone_number_id : 'Conexão pendente de confirmação' }}
                            </div>
                            <div class="mt-1">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colors[$integracao->status] }}">
                                    {{ $labels[$integracao->status] }}
                                </span>
                                @if ($integracao->last_checked_at)
                                    <span class="text-xs text-gray-400 ml-2">
                                        último teste: {{ $integracao->last_checked_at->diffForHumans() }}
                                    </span>
                                @endif
                            </div>
                            @if ($integracao->status === 'erro' && $integracao->last_error)
                                <div class="text-sm text-red-600 mt-2">{{ $integracao->last_error }}</div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <form action="{{ route('clientes.whatsapp.testar', [$cliente, $integracao]) }}" method="POST">
                                @csrf
                                <x-secondary-button type="submit">Testar conexão</x-secondary-button>
                            </form>
                            <form action="{{ route('clientes.whatsapp.desconectar', [$cliente, $integracao]) }}" method="POST"
                                  onsubmit="return confirm('Desconectar este número? A IA para de atender por ele até conectar de novo.')">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 whitespace-nowrap">
                                    Desconectar
                                </button>
                            </form>
                        </div>
                    </div>

                    @if ($integracao->phone_number_id)
                        <div class="pt-4 border-t border-gray-100">
                            <h3 class="text-sm font-medium text-gray-700 mb-2">Enviar mensagem de teste</h3>
                            <form action="{{ route('clientes.whatsapp.enviar-teste', [$cliente, $integracao]) }}" method="POST" class="flex flex-wrap gap-3 items-start">
                                @csrf
                                <div>
                                    <x-text-input name="numero_teste" placeholder="Ex: 5527999999999" class="block w-56" required value="{{ old('numero_teste') }}" />
                                    <x-input-error :messages="$errors->get('numero_teste')" class="mt-1" />
                                </div>
                                <x-secondary-button type="submit">Enviar</x-secondary-button>
                            </form>
                            <p class="text-xs text-gray-400 mt-2">Envia uma mensagem de texto real via WhatsApp Cloud API para o número informado (com DDI e DDD, só números).</p>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-gray-100">
                        <form action="{{ route('clientes.whatsapp.modo-equipe', [$cliente, $integracao]) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="flex items-start gap-2">
                                <input type="checkbox" onchange="this.form.submit()" @checked($integracao->modo_equipe_agentes) class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700">
                                    Os agentes deste número trabalham em equipe (podem transferir a conversa entre si)
                                    <span class="block text-xs text-gray-400 mt-0.5">Quando ligado, uma conversa nova com 2+ agentes ativos é roteada por classificação de IA (usando o objetivo de cada agente), e o agente atual pode transferir a conversa pra outro no meio do atendimento. Desligado, o agente de menor id sempre atende, sem custo extra.</span>
                                </span>
                            </label>
                        </form>

                        @if ($integracao->modo_equipe_agentes)
                            <form action="{{ route('clientes.whatsapp.prompt-classificacao', [$cliente, $integracao]) }}" method="POST" class="mt-4">
                                @csrf
                                @method('PUT')
                                <x-input-label for="prompt_classificacao_extra_{{ $integracao->id }}" value="Instruções adicionais para a classificação de agentes (opcional)" />
                                <textarea id="prompt_classificacao_extra_{{ $integracao->id }}" name="prompt_classificacao_extra" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Ex: Se o contato mencionar um número de pedido, prefira sempre o agente de Pós-venda, mesmo que outro agente também pareça adequado.">{{ old('prompt_classificacao_extra', $integracao->prompt_classificacao_extra ?? '') }}</textarea>
                                <p class="text-xs text-gray-400 mt-1">Some às instruções padrão que a IA já usa pra decidir qual agente atende cada conversa nova neste número.</p>
                                <x-input-error :messages="$errors->get('prompt_classificacao_extra')" class="mt-2" />
                                <div class="mt-2">
                                    <x-secondary-button type="submit">Salvar instruções</x-secondary-button>
                                </div>
                            </form>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <button type="button" @click="credenciaisAbertas = !credenciaisAbertas" class="text-sm font-medium text-gray-700 flex items-center gap-1">
                            Credenciais manuais
                            <x-icon name="chevron-down" class="w-4 h-4 text-gray-400 transition-transform" x-bind:class="{ 'rotate-180': credenciaisAbertas }" />
                        </button>

                        <div x-show="credenciaisAbertas" x-cloak class="mt-4">
                            <form method="POST" action="{{ route('clientes.whatsapp.update', [$cliente, $integracao]) }}">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <x-input-label for="app_id_{{ $integracao->id }}" value="App ID" />
                                        <x-text-input id="app_id_{{ $integracao->id }}" name="app_id" class="mt-1 block w-full" value="{{ old('app_id', $integracao->app_id ?? '') }}" />
                                        <x-input-error :messages="$errors->get('app_id')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="app_secret_{{ $integracao->id }}" value="App Secret" />
                                        <x-text-input id="app_secret_{{ $integracao->id }}" type="password" name="app_secret" class="mt-1 block w-full" value="{{ old('app_secret') }}" autocomplete="new-password" placeholder="{{ $integracao->app_secret ? '••••••••  (deixe em branco para manter)' : '' }}" />
                                        <x-input-error :messages="$errors->get('app_secret')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="business_account_id_{{ $integracao->id }}" value="Business Account ID (WABA ID)" />
                                        <x-text-input id="business_account_id_{{ $integracao->id }}" name="business_account_id" class="mt-1 block w-full" value="{{ old('business_account_id', $integracao->business_account_id ?? '') }}" />
                                        <x-input-error :messages="$errors->get('business_account_id')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="phone_number_id_{{ $integracao->id }}" value="Phone Number ID" />
                                        <x-text-input id="phone_number_id_{{ $integracao->id }}" name="phone_number_id" class="mt-1 block w-full" value="{{ old('phone_number_id', $integracao->phone_number_id ?? '') }}" />
                                        <x-input-error :messages="$errors->get('phone_number_id')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="access_token_{{ $integracao->id }}" value="Access Token" />
                                        <x-text-input id="access_token_{{ $integracao->id }}" type="password" name="access_token" class="mt-1 block w-full" value="{{ old('access_token') }}" autocomplete="new-password" placeholder="{{ $integracao->access_token ? '••••••••  (deixe em branco para manter)' : '' }}" />
                                        <x-input-error :messages="$errors->get('access_token')" class="mt-2" />
                                        <p class="text-xs text-gray-400 mt-1">Armazenado de forma criptografada no banco de dados.</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4 mt-6">
                                    <x-primary-button>{{ __('Salvar credenciais') }}</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <h3 class="text-sm font-medium text-gray-700 mb-1">Token de API — histórico de conversas</h3>
                        <p class="text-xs text-gray-400 mb-4">Usado pelo n8n para gravar cada mensagem da conversa neste número. Só o hash fica salvo no banco — o token só aparece uma vez, na hora em que é gerado.</p>

                        @if (session('novo_api_token') && session('novo_api_token_integracao_id') === $integracao->id)
                            <div class="bg-amber-50 border border-amber-300 rounded-md p-4 mb-4">
                                <p class="text-sm text-amber-800 font-medium mb-2">Guarde este token agora — ele não será mostrado novamente:</p>
                                <input type="text" readonly value="{{ session('novo_api_token') }}" onclick="this.select()" class="block w-full font-mono text-sm border-amber-300 rounded-md bg-white">
                            </div>
                        @endif

                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-500">
                                @if ($integracao->api_token_gerado_em)
                                    Último token gerado {{ $integracao->api_token_gerado_em->diffForHumans() }}.
                                @else
                                    Nenhum token gerado ainda.
                                @endif
                            </p>
                            <form action="{{ route('clientes.whatsapp.token', [$cliente, $integracao]) }}" method="POST" onsubmit="return {{ $integracao->api_token_gerado_em ? "confirm('Gerar um novo token invalida o anterior — o n8n vai precisar ser atualizado. Continuar?')" : 'true' }}">
                                @csrf
                                <x-secondary-button type="submit">{{ $integracao->api_token_gerado_em ? 'Renovar token' : 'Gerar token' }}</x-secondary-button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-6 text-sm text-gray-500">
                    Nenhum número conectado ainda.
                </div>
            @endforelse

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Adicionar número via credenciais manuais</h3>
                <form method="POST" action="{{ route('clientes.whatsapp.store', $cliente) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <x-input-label for="novo_app_id" value="App ID" />
                            <x-text-input id="novo_app_id" name="app_id" class="mt-1 block w-full" value="{{ old('app_id') }}" />
                            <x-input-error :messages="$errors->get('app_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="novo_app_secret" value="App Secret" />
                            <x-text-input id="novo_app_secret" type="password" name="app_secret" class="mt-1 block w-full" value="{{ old('app_secret') }}" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('app_secret')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="novo_business_account_id" value="Business Account ID (WABA ID)" />
                            <x-text-input id="novo_business_account_id" name="business_account_id" class="mt-1 block w-full" value="{{ old('business_account_id') }}" />
                            <x-input-error :messages="$errors->get('business_account_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="novo_phone_number_id" value="Phone Number ID" />
                            <x-text-input id="novo_phone_number_id" name="phone_number_id" class="mt-1 block w-full" value="{{ old('phone_number_id') }}" />
                            <x-input-error :messages="$errors->get('phone_number_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="novo_access_token" value="Access Token" />
                            <x-text-input id="novo_access_token" type="password" name="access_token" class="mt-1 block w-full" value="{{ old('access_token') }}" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('access_token')" class="mt-2" />
                            <p class="text-xs text-gray-400 mt-1">Armazenado de forma criptografada no banco de dados.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-6">
                        <x-primary-button>{{ __('Adicionar número') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="fb-root"></div>
    <script>
        window.fbAsyncInit = function () {
            FB.init({
                appId: '{{ config('services.meta.app_id') }}',
                autoLogAppEvents: true,
                xfbml: false,
                version: '{{ config('services.meta.graph_version') }}',
            });
        };
    </script>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/pt_BR/sdk.js"></script>

    <script>
        (function () {
            let sessaoWaba = null;
            let sessaoPhoneNumberId = null;
            let sessaoErro = null;

            window.addEventListener('message', (event) => {
                if (!event.origin.endsWith('facebook.com')) return;

                // Algumas versões do SDK da Meta mandam event.data já como
                // objeto, não como string JSON — JSON.parse num objeto
                // lança erro e o catch antigo engolia isso em silêncio,
                // sem log nenhum. Aceita os dois formatos.
                let dados = event.data;
                if (typeof dados === 'string') {
                    try {
                        dados = JSON.parse(dados);
                    } catch (e) {
                        console.log('[Embedded Signup] mensagem não-JSON recebida de', event.origin, ':', event.data);
                        return;
                    }
                }

                // Diagnóstico: loga QUALQUER mensagem de facebook.com, mesmo de
                // um "type" diferente do esperado — pra não perder nada que a
                // Meta mande fora do formato que já conhecemos.
                console.log('[Embedded Signup] mensagem bruta recebida:', dados);

                if (!dados || dados.type !== 'WA_EMBEDDED_SIGNUP') return;

                // Diagnóstico: a Meta tem vários "event" possíveis além de FINISH
                // (ex.: FINISH_ONLY_WABA quando só a conta é selecionada sem
                // confirmar o número, FINISH_WHATSAPP_BUSINESS_APP_ONBOARDING
                // pra números de Coexistência) — logar ajuda a identificar qual
                // caiu quando a conexão falha.
                console.log('[Embedded Signup] evento:', dados.event, dados.data);

                if (dados.event === 'CANCEL' && dados.data && dados.data.error_message) {
                    sessaoErro = dados.data.error_message;
                } else if (dados.event === 'FINISH_ONLY_WABA') {
                    sessaoErro = 'A conta foi selecionada, mas o número de telefone não foi confirmado no popup. Clique em "Conectar via WhatsApp" novamente e conclua todos os passos até o final, incluindo a confirmação do número.';
                }

                if (dados.data && dados.data.waba_id) {
                    sessaoWaba = dados.data.waba_id;
                }
                if (dados.data && dados.data.phone_number_id) {
                    sessaoPhoneNumberId = dados.data.phone_number_id;
                }
            });

            function mostrarAlerta(mensagem, tipo) {
                const el = document.getElementById('whatsapp-alert');
                el.textContent = mensagem;
                el.className = 'text-sm rounded-md p-4 mt-4 ' + (tipo === 'erro'
                    ? 'bg-red-100 border border-red-300 text-red-800'
                    : 'bg-green-100 border border-green-300 text-green-800');
                el.classList.remove('hidden');
            }

            document.getElementById('btn-conectar-whatsapp').addEventListener('click', function () {
                sessaoWaba = null;
                sessaoPhoneNumberId = null;
                sessaoErro = null;

                FB.login(function (response) {
                    if (!response.authResponse || !response.authResponse.code) {
                        mostrarAlerta(sessaoErro || 'Conexão cancelada ou não concluída.', 'erro');
                        return;
                    }

                    // Caminho ideal: o popup entregou waba_id/phone_number_id
                    // via postMessage, finaliza tudo na hora.
                    const url = (sessaoWaba && sessaoPhoneNumberId)
                        ? '{{ route('clientes.whatsapp.conectar-embedded', $cliente) }}'
                        : '{{ route('clientes.whatsapp.iniciar-conexao', $cliente) }}';

                    const conexaoImediata = !!(sessaoWaba && sessaoPhoneNumberId);
                    const body = conexaoImediata
                        ? { code: response.authResponse.code, waba_id: sessaoWaba, phone_number_id: sessaoPhoneNumberId }
                        : { code: response.authResponse.code };

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(body),
                    })
                        .then((res) => res.json().then((data) => ({ ok: res.ok, data })))
                        .then(({ ok, data }) => {
                            if (ok) {
                                mostrarAlerta(data.message, 'sucesso');
                                // Conexão imediata (postMessage funcionou): já está pronto,
                                // recarrega rápido. Conexão pendente (webhook ainda vai
                                // confirmar): dá tempo real de ler antes de recarregar.
                                setTimeout(() => window.location.reload(), conexaoImediata ? 1500 : 20000);
                            } else {
                                mostrarAlerta(data.message || 'Erro ao conectar.', 'erro');
                            }
                        })
                        .catch(() => mostrarAlerta('Erro de rede ao conectar. Tente novamente.', 'erro'));
                }, {
                    config_id: '{{ config('services.meta.embedded_signup_config_id') }}',
                    response_type: 'code',
                    override_default_response_type: true,
                    extras: {
                        setup: {},
                        featureType: 'whatsapp_business_app_onboarding',
                        sessionInfoVersion: '3',
                    },
                });
            });
        })();
    </script>
</x-app-layout>
