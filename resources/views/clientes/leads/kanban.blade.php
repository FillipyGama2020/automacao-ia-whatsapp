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
                    <a href="{{ route('clientes.leads.index', array_merge(['cliente' => $cliente], request()->query())) }}"
                       class="text-sm text-indigo-600 hover:text-indigo-900 underline">
                        Ver como tabela
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
                        <x-input-label for="data_inicio" value="De" />
                        <x-text-input id="data_inicio" type="date" name="data_inicio" value="{{ request('data_inicio') }}" class="mt-1 block w-40" />
                    </div>

                    <div>
                        <x-input-label for="data_fim" value="Até" />
                        <x-text-input id="data_fim" type="date" name="data_fim" value="{{ request('data_fim') }}" class="mt-1 block w-40" />
                    </div>

                    <x-secondary-button type="submit">Filtrar</x-secondary-button>
                    @if (request()->anyFilled(['busca', 'classificacao', 'data_inicio', 'data_fim']))
                        <a href="{{ route('clientes.leads.kanban', $cliente) }}" class="text-sm text-gray-500 underline">Limpar</a>
                    @endif
                </form>
            </div>

            @php
                $colunas = \App\Models\Lead::statusLabels();
                $coresClassificacao = [
                    'frio' => 'bg-blue-100 text-blue-800',
                    'morno' => 'bg-amber-100 text-amber-800',
                    'quente' => 'bg-red-100 text-red-800',
                ];
                $corTopoColuna = [
                    'novo' => 'border-t-gray-400',
                    'em_contato' => 'border-t-blue-500',
                    'convertido' => 'border-t-green-500',
                    'perdido' => 'border-t-gray-300',
                ];
            @endphp

            <div id="kanban-erro" class="hidden bg-red-100 border border-red-300 text-red-800 text-sm rounded-md p-4"></div>

            <div class="flex gap-4 overflow-x-auto pb-4" id="kanban-board" data-status-url="{{ route('clientes.leads.status', [$cliente, '__ID__']) }}">
                @foreach ($colunas as $status => $label)
                    <div class="flex-shrink-0 w-72 bg-gray-50 border-t-4 {{ $corTopoColuna[$status] }} rounded-lg shadow-sm">
                        <div class="px-3 py-2 flex items-center justify-between border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-700">{{ $label }}</h3>
                            <span class="text-xs text-gray-400 bg-gray-200 rounded-full px-2 py-0.5" data-coluna-contador>
                                {{ ($leads[$status] ?? collect())->count() }}
                            </span>
                        </div>

                        <div class="p-2 space-y-2 min-h-[120px]" data-coluna="{{ $status }}">
                            @forelse (($leads[$status] ?? collect()) as $lead)
                                <div class="bg-white border border-gray-200 rounded-md shadow-sm p-3 cursor-move hover:shadow-md transition-shadow"
                                     draggable="true"
                                     data-lead-id="{{ $lead->id }}">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="text-sm font-medium text-gray-900">{{ $lead->nome ?? 'Sem nome' }}</div>
                                        <a href="{{ route('clientes.leads.edit', [$cliente, $lead]) }}" title="Editar" class="text-gray-300 hover:text-indigo-600 flex-shrink-0">
                                            <x-icon name="edit" class="w-3.5 h-3.5" />
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">{{ $lead->telefone }}</div>
                                    @if ($lead->interesse)
                                        <div class="text-xs text-gray-400 mt-1 truncate">{{ $lead->interesse }}</div>
                                    @endif
                                    <div class="flex items-center gap-1 mt-2">
                                        @if ($lead->classificacao)
                                            <span class="px-1.5 py-0.5 text-[10px] leading-none font-semibold rounded-full {{ $coresClassificacao[$lead->classificacao] }}">
                                                {{ \App\Models\Lead::classificacaoLabels()[$lead->classificacao] }}
                                            </span>
                                        @endif
                                        @unless ($lead->aceita_campanhas)
                                            <span class="px-1.5 py-0.5 text-[10px] leading-none font-medium rounded-full bg-gray-200 text-gray-600">
                                                Opt-out
                                            </span>
                                        @endunless
                                    </div>
                                </div>
                            @empty
                                <div class="text-xs text-gray-400 text-center py-6" data-coluna-vazia>
                                    Nenhum lead aqui.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        (function () {
            const board = document.getElementById('kanban-board');
            const erroBox = document.getElementById('kanban-erro');
            const statusUrlTemplate = board.dataset.statusUrl;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            let arrastando = null;

            function mostrarErro(msg) {
                erroBox.textContent = msg;
                erroBox.classList.remove('hidden');
                setTimeout(() => erroBox.classList.add('hidden'), 5000);
            }

            function atualizarContadores() {
                board.querySelectorAll('[data-coluna]').forEach((coluna) => {
                    const contador = coluna.closest('div').previousElementSibling?.querySelector('[data-coluna-contador]')
                        ?? coluna.parentElement.querySelector('[data-coluna-contador]');
                    const cards = coluna.querySelectorAll('[data-lead-id]').length;
                    if (contador) contador.textContent = cards;

                    const vazio = coluna.querySelector('[data-coluna-vazia]');
                    if (cards > 0 && vazio) vazio.remove();
                    if (cards === 0 && !coluna.querySelector('[data-coluna-vazia]')) {
                        const p = document.createElement('div');
                        p.className = 'text-xs text-gray-400 text-center py-6';
                        p.setAttribute('data-coluna-vazia', '');
                        p.textContent = 'Nenhum lead aqui.';
                        coluna.appendChild(p);
                    }
                });
            }

            board.addEventListener('dragstart', (e) => {
                const card = e.target.closest('[data-lead-id]');
                if (!card) return;
                arrastando = card;
                e.dataTransfer.effectAllowed = 'move';
                setTimeout(() => card.classList.add('opacity-40'), 0);
            });

            board.addEventListener('dragend', () => {
                if (arrastando) arrastando.classList.remove('opacity-40');
                arrastando = null;
            });

            board.querySelectorAll('[data-coluna]').forEach((coluna) => {
                coluna.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    coluna.classList.add('bg-indigo-50');
                });

                coluna.addEventListener('dragleave', () => {
                    coluna.classList.remove('bg-indigo-50');
                });

                coluna.addEventListener('drop', async (e) => {
                    e.preventDefault();
                    coluna.classList.remove('bg-indigo-50');
                    if (!arrastando) return;

                    const leadId = arrastando.dataset.leadId;
                    const novoStatus = coluna.dataset.coluna;
                    const colunaOrigem = arrastando.parentElement;

                    if (colunaOrigem === coluna) return;

                    coluna.appendChild(arrastando);
                    const vazio = coluna.querySelector('[data-coluna-vazia]');
                    if (vazio) vazio.remove();
                    atualizarContadores();

                    try {
                        const url = statusUrlTemplate.replace('__ID__', leadId);
                        const resposta = await fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ status: novoStatus }),
                        });

                        if (!resposta.ok) throw new Error('Falha ao salvar');
                    } catch (err) {
                        colunaOrigem.appendChild(arrastando);
                        atualizarContadores();
                        mostrarErro('Não foi possível mover o lead. Tente de novo.');
                    }
                });
            });
        })();
    </script>
</x-app-layout>
