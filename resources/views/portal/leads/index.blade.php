<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leads') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-white shadow-sm sm:rounded-lg p-4 flex gap-8">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Total de leads</div>
                    <div class="text-lg font-semibold text-gray-800">{{ $resumo['total'] }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Quentes</div>
                    <div class="text-lg font-semibold text-red-600">{{ $resumo['quentes'] }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Convertidos</div>
                    <div class="text-lg font-semibold text-green-600">{{ $resumo['convertidos'] }}</div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <x-input-label for="busca" value="Buscar (nome, telefone, email)" />
                        <x-text-input id="busca" name="busca" value="{{ request('busca') }}" class="mt-1 block w-64" />
                    </div>

                    <div>
                        <x-input-label for="classificacao" value="Classificação" />
                        <select id="classificacao" name="classificacao" class="mt-1 block w-40 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Todas</option>
                            @foreach (\App\Models\Lead::classificacaoLabels() as $valor => $label)
                                <option value="{{ $valor }}" @selected(request('classificacao') === $valor)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-44 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            @foreach (\App\Models\Lead::statusLabels() as $valor => $label)
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
                    @if (request()->anyFilled(['busca', 'classificacao', 'status', 'data_inicio', 'data_fim']))
                        <a href="{{ route('portal.leads.index') }}" class="text-sm text-gray-500 underline">Limpar</a>
                    @endif
                </form>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interesse</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classificação</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capturado em</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($leads as $lead)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                        {{ $lead->nome ?? 'Sem nome' }}
                                        @unless ($lead->aceita_campanhas)
                                            <span title="Não aceita campanhas{{ $lead->opt_out_em ? ' — desde ' . $lead->opt_out_em->format('d/m/Y') : '' }}" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                                                Opt-out
                                            </span>
                                        @endunless
                                    </div>
                                    <div class="text-xs text-gray-400">{{ \App\Models\Lead::origemLabels()[$lead->origem] }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $lead->telefone }}</div>
                                    <div>{{ $lead->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $lead->interesse }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($lead->classificacao)
                                        @php
                                            $coresClassificacao = [
                                                'frio' => 'bg-blue-100 text-blue-800',
                                                'morno' => 'bg-amber-100 text-amber-800',
                                                'quente' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $coresClassificacao[$lead->classificacao] }}">
                                            {{ \App\Models\Lead::classificacaoLabels()[$lead->classificacao] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $coresStatus = [
                                            'novo' => 'bg-gray-100 text-gray-800',
                                            'em_contato' => 'bg-blue-100 text-blue-800',
                                            'convertido' => 'bg-green-100 text-green-800',
                                            'perdido' => 'bg-gray-100 text-gray-500',
                                        ];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $coresStatus[$lead->status] }}">
                                        {{ \App\Models\Lead::statusLabels()[$lead->status] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lead->capturado_em->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Nenhum lead capturado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $leads->links() }}
        </div>
    </div>
</x-portal-layout>
