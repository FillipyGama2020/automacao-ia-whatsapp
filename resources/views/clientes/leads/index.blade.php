<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('clientes.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="back" class="w-5 h-5" />
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Leads — ') }}{{ $cliente->nome_empresa }}
                </h2>
            </div>
            <a href="{{ route('clientes.leads.create', $cliente) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Novo lead') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

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

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Importar em massa (CSV)</h3>
                <p class="text-xs text-gray-400 mb-3">
                    Arquivo .csv com coluna <code>telefone</code> (obrigatória) e <code>nome</code>, <code>email</code>,
                    <code>interesse</code>, <code>classificacao</code> (opcionais, aceita frio/morno/quente).
                    Leads com telefone já cadastrado para este cliente são ignorados automaticamente.
                </p>
                <form method="POST" action="{{ route('clientes.leads.importar', $cliente) }}" enctype="multipart/form-data" class="flex flex-wrap gap-3 items-center">
                    @csrf
                    <input type="file" name="arquivo" accept=".csv,text/csv" required class="block text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                    <x-secondary-button type="submit">Importar</x-secondary-button>
                </form>
                <x-input-error :messages="$errors->get('arquivo')" class="mt-2" />
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4 flex gap-8 items-center">
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
                <div class="ml-auto">
                    <a href="{{ route('clientes.leads.kanban', array_merge(['cliente' => $cliente], request()->query())) }}"
                       class="text-sm text-indigo-600 hover:text-indigo-900 underline">
                        Ver como Kanban
                    </a>
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
                        <a href="{{ route('clientes.leads.index', $cliente) }}" class="text-sm text-gray-500 underline">Limpar</a>
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
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
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
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('clientes.leads.edit', [$cliente, $lead]) }}" title="Editar"
                                       class="p-1.5 rounded-md text-indigo-600 hover:bg-indigo-50 hover:text-indigo-900 inline-flex">
                                        <x-icon name="edit" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
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
</x-app-layout>
