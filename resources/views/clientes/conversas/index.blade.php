<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.index') }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Conversas — ') }}{{ $cliente->nome_empresa }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-white shadow-sm sm:rounded-lg p-4 flex gap-8">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Conversas neste mês</div>
                    <div class="text-lg font-semibold text-gray-800">{{ $resumoMes->total_conversas }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Custo estimado neste mês</div>
                    <div class="text-lg font-semibold text-gray-800">R$ {{ number_format($resumoMes->total_custo, 2, ',', '.') }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Limite do plano</div>
                    <div class="text-lg font-semibold {{ $limiteConversas['excedido'] ? 'text-red-600' : 'text-gray-800' }}">
                        @if ($limiteConversas['limite'] === null)
                            Ilimitado
                        @else
                            {{ $limiteConversas['atual'] }} / {{ $limiteConversas['limite'] }}
                        @endif
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">Só conta conversa com uso de IA</div>
                </div>
            </div>

            @if ($limiteConversas['excedido'])
                <div class="bg-amber-50 border border-amber-300 text-amber-800 text-sm rounded-md p-4">
                    Este cliente ultrapassou o limite de <strong>{{ $limiteConversas['limite'] }}</strong> conversas com IA do plano {{ $cliente->plano->nome }} — já são <strong>{{ $limiteConversas['atual'] }}</strong> este mês (conversas 100% manuais não entram nessa conta). O atendimento automático continua funcionando normalmente; isto é só um aviso para você avaliar cobrança de excedente ou sugerir upgrade de plano.
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <x-input-label for="busca" value="Buscar no conteúdo" />
                        <x-text-input id="busca" name="busca" value="{{ request('busca') }}" class="mt-1 block w-56" />
                    </div>

                    <div>
                        <x-input-label for="contato_telefone" value="Telefone do contato" />
                        <x-text-input id="contato_telefone" name="contato_telefone" value="{{ request('contato_telefone') }}" class="mt-1 block w-44" />
                    </div>

                    <div>
                        <x-input-label for="agente_id" value="Agente" />
                        <select id="agente_id" name="agente_id" class="mt-1 block w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            @foreach ($agentes as $agente)
                                <option value="{{ $agente->id }}" @selected(request('agente_id') == $agente->id)>{{ $agente->nome }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-44 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            @foreach (\App\Models\Conversa::statusLabels() as $valor => $label)
                                <option value="{{ $valor }}" @selected(request('status') === $valor)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="data_inicio" value="De" />
                        <x-text-input id="data_inicio" type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="mt-1 block w-40" />
                    </div>

                    <div>
                        <x-input-label for="data_fim" value="Até" />
                        <x-text-input id="data_fim" type="date" name="data_fim" value="{{ request('data_fim') }}" class="mt-1 block w-40" />
                    </div>

                    <x-secondary-button type="submit">Filtrar</x-secondary-button>
                    @if (request()->anyFilled(['busca', 'contato_telefone', 'agente_id', 'status', 'data_inicio', 'data_fim']))
                        <a href="{{ route('clientes.conversas.index', $cliente) }}" class="text-sm text-gray-500 underline">Limpar</a>
                    @endif

                    <a href="{{ route('clientes.conversas.exportar', array_merge(['cliente' => $cliente], request()->query())) }}"
                       class="ml-auto inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50">
                        Exportar CSV
                    </a>
                </form>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mensagens</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Custo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última atividade</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($conversas as $conversa)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 truncate max-w-44" title="{{ $conversa->contato_nome ?? 'Sem nome' }}">{{ $conversa->contato_nome ?? 'Sem nome' }}</div>
                                    <div class="text-sm text-gray-500">{{ $conversa->contato_telefone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $conversa->agente->nome ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $cores = [
                                            'em_andamento' => 'bg-blue-100 text-blue-800',
                                            'resolvida_ia' => 'bg-green-100 text-green-800',
                                            'transferida_humano' => 'bg-amber-100 text-amber-800',
                                            'abandonada' => 'bg-gray-100 text-gray-800',
                                        ];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cores[$conversa->status] }}">
                                        {{ \App\Models\Conversa::statusLabels()[$conversa->status] }}
                                    </span>
                                    @if ($conversa->avaliacao)
                                        <span class="text-xs text-gray-400 ml-1">★ {{ $conversa->avaliacao }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $conversa->mensagens_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($conversa->custo_estimado, 4, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $conversa->ultima_mensagem_em?->diffForHumans() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('clientes.conversas.show', [$cliente, $conversa]) }}" title="Ver conversa"
                                       class="p-1.5 rounded-md text-indigo-600 hover:bg-indigo-50 hover:text-indigo-900 inline-flex">
                                        <x-icon name="eye" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Nenhuma conversa encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $conversas->links() }}
        </div>
    </div>
</x-app-layout>
