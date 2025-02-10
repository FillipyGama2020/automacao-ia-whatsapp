<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('LGPD — Direito ao esquecimento') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

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

            <div class="bg-amber-50 border border-amber-300 rounded-md p-4 text-sm text-amber-800">
                Retenção automática: conversas com mais de <strong>12 meses</strong> são apagadas sozinhas todo dia (mensagem, mídia e tudo mais). Use esta tela só para atender um pedido explícito de exclusão de um contato (direito ao esquecimento da LGPD) — a ação aqui é <strong>permanente e não pode ser desfeita</strong>.
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="flex gap-3 items-end">
                    <div class="flex-1">
                        <x-input-label for="telefone" value="Telefone do contato" />
                        <x-text-input id="telefone" name="telefone" class="mt-1 block w-full" value="{{ $telefone }}" placeholder="+5511999999999" />
                        <p class="mt-1 text-xs text-gray-500">Pode digitar com ou sem +55, com espaço/traço/parênteses — a busca ignora a formatação.</p>
                    </div>
                    <x-secondary-button type="submit">Buscar</x-secondary-button>
                </form>
            </div>

            @if ($telefone)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-4">
                        {{ $conversas->count() }} conversa(s) e {{ $leads->count() }} lead(s) encontrado(s) para {{ $telefone }}
                    </h3>

                    @if ($conversas->isNotEmpty())
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Conversas</p>
                        <ul class="divide-y divide-gray-100 mb-6">
                            @foreach ($conversas as $conversa)
                                <li class="py-2 text-sm text-gray-600 flex justify-between">
                                    <span>{{ $conversa->cliente->nome_empresa ?? '—' }} · {{ $conversa->agente->nome ?? '—' }} · {{ \App\Models\Conversa::statusLabels()[$conversa->status] }}</span>
                                    <span class="text-gray-400">{{ $conversa->iniciada_em->format('d/m/Y') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($leads->isNotEmpty())
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Leads</p>
                        <ul class="divide-y divide-gray-100 mb-6">
                            @foreach ($leads as $lead)
                                <li class="py-2 text-sm text-gray-600 flex justify-between">
                                    <span>{{ $lead->cliente->nome_empresa ?? '—' }} · {{ $lead->nome ?? 'Sem nome' }} · {{ \App\Models\Lead::statusLabels()[$lead->status] }}</span>
                                    <span class="text-gray-400">{{ $lead->capturado_em->format('d/m/Y') }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    @if ($conversas->isNotEmpty() || $leads->isNotEmpty())
                        <form method="POST" action="{{ route('lgpd.destroy') }}" onsubmit="return confirm('Confirma a exclusão PERMANENTE de {{ $conversas->count() }} conversa(s) e {{ $leads->count() }} lead(s) deste telefone? Não é possível desfazer.');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="telefone" value="{{ $telefone }}">
                            <label class="flex items-center gap-2 text-sm text-gray-700 mb-4">
                                <input type="checkbox" name="confirmo" value="1" required class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                Confirmo que este é um pedido válido de exclusão e entendo que a ação é irreversível.
                            </label>
                            <x-danger-button type="submit">Excluir definitivamente</x-danger-button>
                        </form>
                    @endif
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conversas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensagens</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leads</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Executado por</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($historico as $log)
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $log->executado_em->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $log->contato_telefone ?? '(vários — expurgo automático)' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $log->motivo === 'retencao_automatica' ? 'Retenção automática (12 meses)' : 'Solicitação do titular' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $log->quantidade_conversas }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $log->quantidade_mensagens }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $log->quantidade_leads }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $log->executadoPor->name ?? 'Sistema' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Nenhuma exclusão registrada ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
