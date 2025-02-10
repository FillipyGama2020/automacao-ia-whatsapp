<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.index') }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Financeiro — ') }}{{ $cliente->nome_empresa }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if ($competenciasDisponiveis->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-sm text-gray-500">
                    Nenhum fechamento financeiro para este cliente ainda.
                    <a href="{{ route('financeiro.fechamentos.index') }}" class="text-indigo-600 underline">Feche um mês</a> pra ver os dados aqui.
                </div>
            @else

                <div class="bg-white shadow-sm sm:rounded-lg p-4">
                    <form method="GET" class="flex gap-3 items-end">
                        <div>
                            <x-input-label for="competencia" value="Competência" />
                            <select id="competencia" name="competencia" onchange="this.form.submit()" class="mt-1 block w-40 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach ($competenciasDisponiveis as $c)
                                    <option value="{{ $c->format('Y-m') }}" @selected($c->isSameMonth($competencia))>{{ $c->format('m/Y') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                @if (! $fechamento)
                    <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-sm text-gray-500">
                        Nenhum fechamento encontrado para {{ $competencia->format('m/Y') }}.
                    </div>
                @else

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white shadow-sm sm:rounded-lg p-4">
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Receita total</div>
                            <div class="text-lg font-semibold text-gray-800">R$ {{ number_format($fechamento->receitaTotal(), 2, ',', '.') }}</div>
                        </div>
                        <div class="bg-white shadow-sm sm:rounded-lg p-4">
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Custo total</div>
                            <div class="text-lg font-semibold text-gray-800">R$ {{ number_format($fechamento->custoTotal(), 2, ',', '.') }}</div>
                        </div>
                        <div class="bg-white shadow-sm sm:rounded-lg p-4">
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Lucro bruto</div>
                            <div class="text-lg font-semibold {{ $fechamento->lucro_bruto >= 0 ? 'text-green-700' : 'text-red-700' }}">R$ {{ number_format($fechamento->lucro_bruto, 2, ',', '.') }}</div>
                        </div>
                        <div class="bg-white shadow-sm sm:rounded-lg p-4">
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Margem</div>
                            <div class="text-lg font-semibold text-gray-800">{{ $fechamento->margem_percentual !== null ? number_format($fechamento->margem_percentual, 1, ',', '.').'%' : '—' }}</div>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-5">
                        <h3 class="text-sm font-medium text-gray-700">Consumo do plano em {{ $competencia->format('m/Y') }}</h3>

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Conversas com uso de IA</span>
                                <span class="text-gray-500">
                                    @if ($fechamento->limite_conversas_plano === null)
                                        {{ $fechamento->conversas_no_mes }} (plano ilimitado)
                                    @else
                                        {{ $fechamento->conversas_no_mes }} / {{ $fechamento->limite_conversas_plano }}
                                        ({{ number_format($fechamento->percentualConversas(), 0, ',', '.') }}%)
                                    @endif
                                </span>
                            </div>
                            @if ($fechamento->limite_conversas_plano !== null)
                                @php
                                    $pctConversas = $fechamento->percentualConversas();
                                    $corConversas = $pctConversas >= 100 ? 'bg-red-500' : ($pctConversas >= 80 ? 'bg-amber-500' : 'bg-green-500');
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full {{ $corConversas }}" style="width: {{ min(100, $pctConversas) }}%"></div>
                                </div>
                                @if ($fechamento->conversas_excedentes > 0)
                                    <p class="text-xs text-red-600 mt-1">
                                        Ultrapassou em {{ $fechamento->conversas_excedentes }} conversa(s) — R$ {{ number_format($fechamento->valor_conversas_excedentes, 2, ',', '.') }} a mais na fatura.
                                    </p>
                                @endif
                            @endif
                        </div>

                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Agentes de IA</span>
                                <span class="text-gray-500">
                                    @if ($fechamento->limite_agentes_plano === null)
                                        {{ $fechamento->agentes_no_mes }} (plano ilimitado)
                                    @else
                                        {{ $fechamento->agentes_no_mes }} / {{ $fechamento->limite_agentes_plano }}
                                        ({{ number_format($fechamento->percentualAgentes(), 0, ',', '.') }}%)
                                    @endif
                                </span>
                            </div>
                            @if ($fechamento->limite_agentes_plano !== null)
                                @php
                                    $pctAgentes = $fechamento->percentualAgentes();
                                    $corAgentes = $pctAgentes >= 100 ? 'bg-red-500' : ($pctAgentes >= 80 ? 'bg-amber-500' : 'bg-green-500');
                                @endphp
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full {{ $corAgentes }}" style="width: {{ min(100, $pctAgentes) }}%"></div>
                                </div>
                                @if ($fechamento->agentes_extras > 0)
                                    <p class="text-xs text-red-600 mt-1">
                                        {{ $fechamento->agentes_extras }} agente(s) extra — R$ {{ number_format($fechamento->valor_agentes_extras, 2, ',', '.') }} a mais na fatura.
                                    </p>
                                @endif
                            @endif
                        </div>

                        @if ($fechamento->anexos_cobrados)
                            <div class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md p-3">
                                Anexos habilitados fora do incluso no plano — R$ {{ number_format($fechamento->valor_anexos, 2, ',', '.') }} a mais na fatura.
                            </div>
                        @endif

                        @if ($fechamento->receita_excedente > 0)
                            <div class="border-t border-gray-100 pt-3 flex justify-between text-sm font-medium">
                                <span class="text-gray-700">Total de excedente no mês</span>
                                <span class="text-red-600">R$ {{ number_format($fechamento->receita_excedente, 2, ',', '.') }}</span>
                            </div>
                        @endif

                        @if ($fechamento->receita_campanhas > 0)
                            <div class="border-t border-gray-100 pt-3 flex justify-between text-sm font-medium">
                                <span class="text-gray-700">Campanhas (Mensagens Proativas) no mês</span>
                                <span class="text-gray-800">R$ {{ number_format($fechamento->receita_campanhas, 2, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
