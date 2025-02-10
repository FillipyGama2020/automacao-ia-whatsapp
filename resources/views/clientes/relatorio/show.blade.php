<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.index') }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Relatório — ') }}{{ $cliente->nome_empresa }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="flex gap-3 items-end">
                    <div>
                        <x-input-label for="competencia" value="Competência" />
                        <input type="month" id="competencia" name="competencia" value="{{ $competencia->format('Y-m') }}" class="mt-1 block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    </div>
                    <x-secondary-button type="submit">Gerar</x-secondary-button>
                    <a href="{{ route('clientes.relatorio.pdf', ['cliente' => $cliente, 'competencia' => $competencia->format('Y-m')]) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                        Baixar PDF
                    </a>
                </form>
            </div>

            <div class="text-sm text-gray-500">Período: {{ $competencia->format('m/Y') }}</div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Atendimento</h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wider">Conversas</div>
                        <div class="text-lg font-semibold text-gray-800">{{ $relatorio['atendimento']['total'] }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wider">Avaliação média</div>
                        <div class="text-lg font-semibold text-gray-800">{{ $relatorio['atendimento']['avaliacao_media'] !== null ? number_format($relatorio['atendimento']['avaliacao_media'], 1, ',', '.').' ★' : '—' }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-400 uppercase tracking-wider">Custo de IA</div>
                        <div class="text-lg font-semibold text-gray-800">R$ {{ number_format($relatorio['custo_ia'], 4, ',', '.') }}</div>
                    </div>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($relatorio['atendimento']['por_status'] as $label => $total)
                            <tr>
                                <td class="py-1.5 text-gray-600">{{ $label }}</td>
                                <td class="py-1.5 text-right text-gray-800">{{ $total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Mensagens ({{ $relatorio['mensagens']['total'] }} no período)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Por tipo</p>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($relatorio['mensagens']['por_tipo'] as $label => $total)
                                    @if ($total > 0)
                                        <tr>
                                            <td class="py-1.5 text-gray-600">{{ $label }}</td>
                                            <td class="py-1.5 text-right text-gray-800">{{ $total }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Por remetente</p>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($relatorio['mensagens']['por_remetente'] as $label => $total)
                                    @if ($total > 0)
                                        <tr>
                                            <td class="py-1.5 text-gray-600">{{ $label }}</td>
                                            <td class="py-1.5 text-right text-gray-800">{{ $total }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Leads capturados ({{ $relatorio['leads']['total'] }} no período)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Por classificação</p>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($relatorio['leads']['por_classificacao'] as $label => $total)
                                    <tr>
                                        <td class="py-1.5 text-gray-600">{{ $label }}</td>
                                        <td class="py-1.5 text-right text-gray-800">{{ $total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Por status</p>
                        <table class="w-full text-sm">
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($relatorio['leads']['por_status'] as $label => $total)
                                    <tr>
                                        <td class="py-1.5 text-gray-600">{{ $label }}</td>
                                        <td class="py-1.5 text-right text-gray-800">{{ $total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Mensagens por horário do dia</h3>
                @php
                    $maiorHora = max($relatorio['horarios']) ?: 1;
                @endphp
                <div class="space-y-1">
                    @foreach ($relatorio['horarios'] as $hora => $total)
                        @if ($total > 0)
                            <div class="flex items-center gap-2 text-xs">
                                <span class="w-10 text-gray-500 text-right">{{ str_pad($hora, 2, '0', STR_PAD_LEFT) }}h</span>
                                <div class="flex-1 bg-gray-100 rounded">
                                    <div class="bg-indigo-500 h-4 rounded" style="width: {{ max(4, ($total / $maiorHora) * 100) }}%"></div>
                                </div>
                                <span class="w-8 text-gray-600">{{ $total }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Financeiro</h3>
                @if (! $relatorio['financeiro'])
                    <p class="text-sm text-gray-500">
                        Este mês ainda não foi fechado. <a href="{{ route('financeiro.fechamentos.index') }}" class="text-indigo-600 underline">Feche o mês</a> pra ver os dados financeiros aqui.
                    </p>
                @else
                    @php($f = $relatorio['financeiro'])
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Receita total</div>
                            <div class="text-base font-semibold text-gray-800">R$ {{ number_format($f->receitaTotal(), 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Custo total</div>
                            <div class="text-base font-semibold text-gray-800">R$ {{ number_format($f->custoTotal(), 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Lucro bruto</div>
                            <div class="text-base font-semibold {{ $f->lucro_bruto >= 0 ? 'text-green-700' : 'text-red-700' }}">R$ {{ number_format($f->lucro_bruto, 2, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Margem</div>
                            <div class="text-base font-semibold text-gray-800">{{ $f->margem_percentual !== null ? number_format($f->margem_percentual, 1, ',', '.').'%' : '—' }}</div>
                        </div>
                    </div>
                    @if ($f->receita_excedente > 0)
                        <p class="text-xs text-red-600 mt-3">Inclui R$ {{ number_format($f->receita_excedente, 2, ',', '.') }} de excedente sobre o plano contratado.</p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
