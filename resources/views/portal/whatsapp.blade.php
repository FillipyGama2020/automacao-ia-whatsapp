<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('WhatsApp') }}
        </h2>
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

            <div id="whatsapp-alert" class="hidden text-sm rounded-md p-4"></div>

            @php
                $labels = ['conectado' => 'Conectado', 'erro' => 'Erro', 'nao_conectado' => 'Não conectado'];
                $colors = [
                    'conectado' => 'bg-green-100 text-green-800',
                    'erro' => 'bg-red-100 text-red-800',
                    'nao_conectado' => 'bg-gray-100 text-gray-800',
                ];
            @endphp

            @foreach ($integracoes as $integracao)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Status da conexão</div>
                            <div class="mt-1">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colors[$integracao->status] }}">
                                    {{ $labels[$integracao->status] }}
                                </span>
                            </div>
                            @if ($integracao->phone_number_id)
                                <p class="text-xs text-gray-400 mt-2">Número conectado — ID {{ $integracao->phone_number_id }}</p>
                            @else
                                <p class="text-xs text-gray-400 mt-2">Conexão pendente de confirmação.</p>
                            @endif
                        </div>

                        <form action="{{ route('portal.whatsapp.desconectar', $integracao) }}" method="POST"
                              onsubmit="return confirm('Desconectar este número? A IA para de atender por ele até você conectar de novo.')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                                Desconectar
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-700">
                            {{ $integracoes->isEmpty() ? 'Conectar WhatsApp' : 'Conectar outro número' }}
                        </h3>
                        <p class="text-xs text-gray-400 mt-1">
                            Ao clicar, você vai fazer login com a conta do Facebook/Business ligada ao seu WhatsApp e
                            escanear um QR code pelo próprio aplicativo WhatsApp Business do seu celular — sem perder o
                            histórico de conversas que já tem por lá.
                        </p>
                    </div>

                    <button type="button" id="btn-conectar-whatsapp" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 whitespace-nowrap">
                        {{ $integracoes->isEmpty() ? 'Conectar via WhatsApp' : 'Conectar outro número' }}
                    </button>
                </div>
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
                el.className = 'text-sm rounded-md p-4 ' + (tipo === 'erro'
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
                        ? '{{ route('portal.whatsapp.conectar-embedded') }}'
                        : '{{ route('portal.whatsapp.iniciar-conexao') }}';

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
</x-portal-layout>
