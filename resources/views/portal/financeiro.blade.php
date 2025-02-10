<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financeiro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if ($consumoAtual['excedido'])
                <div class="bg-amber-50 border border-amber-300 text-amber-800 text-sm rounded-md p-4">
                    Você já usou <strong>{{ $consumoAtual['atual'] }}</strong> das <strong>{{ $consumoAtual['limite'] }}</strong> conversas com uso de IA incluídas no seu plano este mês. Conversas excedentes podem gerar cobrança adicional — veja o detalhamento abaixo.
                </div>
            @elseif ($consumoAtual['limite'] !== null)
                <div class="bg-white shadow-sm sm:rounded-lg p-4 text-sm text-gray-600">
                    Consumo deste mês: <strong>{{ $consumoAtual['atual'] }}</strong> de <strong>{{ $consumoAtual['limite'] }}</strong> conversas com uso de IA incluídas no plano.
                </div>
            @endif

            @if ($competenciasDisponiveis->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-sm text-gray-500">
                    Nenhum fechamento financeiro disponível ainda.
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

                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-4">Cobrança de {{ $competencia->format('m/Y') }}</h3>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-gray-600">Mensalidade do plano</dt>
                                <dd class="text-gray-800">R$ {{ number_format($fechamento->receita_recorrente, 2, ',', '.') }}</dd>
                            </div>
                            @if ($fechamento->receita_implantacao > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Taxa de implantação</dt>
                                    <dd class="text-gray-800">R$ {{ number_format($fechamento->receita_implantacao, 2, ',', '.') }}</dd>
                                </div>
                            @endif
                            @if ($fechamento->receita_excedente > 0)
                                <div class="flex justify-between text-red-600">
                                    <dt>Excedente do mês</dt>
                                    <dd>R$ {{ number_format($fechamento->receita_excedente, 2, ',', '.') }}</dd>
                                </div>
                            @endif
                            @if ($fechamento->receita_campanhas > 0)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Campanhas (Mensagens Proativas)</dt>
                                    <dd class="text-gray-800">R$ {{ number_format($fechamento->receita_campanhas, 2, ',', '.') }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between font-semibold border-t border-gray-100 pt-2 mt-2">
                                <dt class="text-gray-700">Total</dt>
                                <dd class="text-gray-900">R$ {{ number_format($fechamento->receitaTotal(), 2, ',', '.') }}</dd>
                            </div>
                        </dl>
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
                                    <p class="text-xs text-red-600 mt-1">Ultrapassou em {{ $fechamento->conversas_excedentes }} conversa(s).</p>
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
                            @endif
                        </div>

                        @if ($fechamento->anexos_cobrados)
                            <div class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-md p-3">
                                Este mês inclui cobrança adicional por anexos habilitados fora do plano contratado.
                            </div>
                        @endif
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-portal-layout>
