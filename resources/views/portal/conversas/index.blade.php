<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Conversas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
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
                    @if (request()->anyFilled(['agente_id', 'status', 'data_inicio', 'data_fim']))
                        <a href="{{ route('portal.conversas.index') }}" class="text-sm text-gray-500 underline">Limpar</a>
                    @endif
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $conversa->ultima_mensagem_em?->diffForHumans() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('portal.conversas.show', $conversa) }}" title="Ver conversa"
                                       class="p-1.5 rounded-md text-indigo-600 hover:bg-indigo-50 hover:text-indigo-900 inline-flex">
                                        <x-icon name="eye" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
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
</x-portal-layout>
