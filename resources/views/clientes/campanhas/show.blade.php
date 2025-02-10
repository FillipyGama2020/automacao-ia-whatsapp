<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.campanhas.index', $cliente) }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Campanha — ') }}{{ $campanha->messageTemplate->nome }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            @if (in_array($campanha->status, ['rascunho', 'agendada']))
                <div class="bg-amber-50 border border-amber-300 rounded-md p-4 flex items-center justify-between gap-4">
                    <span class="text-sm text-amber-800">
                        @if ($campanha->status === 'agendada')
                            Agendada pra <strong>{{ $campanha->agendado_para->format('d/m/Y H:i') }}</strong> — dispara sozinha nesse horário, ou clique "Enviar agora" pra disparar antes.
                        @else
                            Rascunho — revise os destinatários e o valor abaixo antes de confirmar o envio.
                        @endif
                    </span>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <form method="POST" action="{{ route('clientes.campanhas.cancelar', [$cliente, $campanha]) }}" onsubmit="return confirm('Cancelar esta campanha? Nenhuma mensagem será enviada.')">
                            @csrf
                            <x-secondary-button type="submit">Cancelar</x-secondary-button>
                        </form>
                        <form method="POST" action="{{ route('clientes.campanhas.enviar', [$cliente, $campanha]) }}" onsubmit="return confirm('Confirmar o envio dessa campanha pra {{ $campanha->total_leads }} destinatário(s), valor R$ {{ number_format($campanha->valor_cobrado, 2, ',', '.') }}? Essa ação não pode ser desfeita.')">
                            @csrf
                            <x-primary-button type="submit">Enviar agora</x-primary-button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-6">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Status</div>
                    <div class="text-sm font-semibold text-gray-800 mt-1">{{ \App\Models\Campanha::statusLabels()[$campanha->status] }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Destinatários</div>
                    <div class="text-sm font-semibold text-gray-800 mt-1">
                        {{ $campanha->total_leads }}
                        <span class="font-normal text-gray-500">({{ $campanha->tipo_destinatario === 'lote' ? \App\Models\Campanha::filtroLoteLabels()[$campanha->filtro_lote] : 'Individual' }})</span>
                    </div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Valor cobrado</div>
                    <div class="text-sm font-semibold text-gray-800 mt-1">R$ {{ number_format($campanha->valor_cobrado, 2, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Categoria do template</div>
                    <div class="text-sm font-semibold text-gray-800 mt-1">{{ \App\Models\MessageTemplate::categoriaLabels()[$campanha->messageTemplate->categoria] }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Texto do template</h3>
                <p class="text-sm text-gray-600 bg-gray-50 rounded-md p-3">{{ $campanha->messageTemplate->corpo }}</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 grid grid-cols-3 gap-6">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Enviados</div>
                    <div class="text-lg font-semibold text-green-600 mt-1">{{ $resumoEnvios['enviado'] ?? 0 }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Falharam</div>
                    <div class="text-lg font-semibold text-red-600 mt-1">{{ $resumoEnvios['falhou'] ?? 0 }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Pendentes</div>
                    <div class="text-lg font-semibold text-gray-600 mt-1">{{ $resumoEnvios['pendente'] ?? 0 }}</div>
                </div>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lead</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status do envio</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($envios as $envio)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $envio->lead->nome ?? 'Sem nome' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $envio->lead->telefone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ \App\Models\CampanhaEnvio::statusLabels()[$envio->status] }}
                                    </span>
                                    @if ($envio->erro)
                                        <span class="text-xs text-red-600 block mt-1">{{ $envio->erro }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">Nenhum destinatário.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $envios->links() }}
        </div>
    </div>
</x-app-layout>
