<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financeiro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @include('financeiro._nav')

            @if ($competenciasDisponiveis->isEmpty())
                <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-sm text-gray-500">
                    Nenhum fechamento financeiro realizado ainda.
                    <a href="{{ route('financeiro.fechamentos.index') }}" class="text-indigo-600 underline">Feche um mês</a> pra ver o dashboard aqui.
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

                @if ($fechamentos->isEmpty())
                    <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-sm text-gray-500">
                        Nenhum fechamento encontrado para {{ $competencia->format('m/Y') }}.
                    </div>
                @else

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-white shadow-sm sm:rounded-lg p-4">
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Receita total</div>
                            <div class="text-lg font-semibold text-gray-800">R$ {{ number_format($consolidado['receita_total'], 2, ',', '.') }}</div>
                        </div>
                        <div class="bg-white shadow-sm sm:rounded-lg p-4">
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Custo total</div>
                            <div class="text-lg font-semibold text-gray-800">R$ {{ number_format($consolidado['custo_total'], 2, ',', '.') }}</div>
                        </div>
                        <div class="bg-white shadow-sm sm:rounded-lg p-4">
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Lucro bruto</div>
                            <div class="text-lg font-semibold {{ $consolidado['lucro_bruto'] >= 0 ? 'text-green-700' : 'text-red-700' }}">R$ {{ number_format($consolidado['lucro_bruto'], 2, ',', '.') }}</div>
                        </div>
                        <div class="bg-white shadow-sm sm:rounded-lg p-4">
                            <div class="text-xs text-gray-400 uppercase tracking-wider">Margem</div>
                            <div class="text-lg font-semibold text-gray-800">{{ $consolidado['margem_percentual'] !== null ? number_format($consolidado['margem_percentual'], 1, ',', '.').'%' : '—' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white shadow-sm sm:rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Receita por origem</h3>
                            <dl class="space-y-1 text-sm">
                                <div class="flex justify-between"><dt class="text-gray-500">Recorrente (mensalidades)</dt><dd class="text-gray-800">R$ {{ number_format($consolidado['receita_recorrente'], 2, ',', '.') }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Implantação</dt><dd class="text-gray-800">R$ {{ number_format($consolidado['receita_implantacao'], 2, ',', '.') }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Excedente de planos</dt><dd class="text-gray-800">R$ {{ number_format($consolidado['receita_excedente'], 2, ',', '.') }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Campanhas (Mensagens Proativas)</dt><dd class="text-gray-800">R$ {{ number_format($consolidado['receita_campanhas'], 2, ',', '.') }}</dd></div>
                            </dl>
                        </div>
                        <div class="bg-white shadow-sm sm:rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-700 mb-3">Custo por origem</h3>
                            <dl class="space-y-1 text-sm">
                                <div class="flex justify-between"><dt class="text-gray-500">IA (OpenAI)</dt><dd class="text-gray-800">R$ {{ number_format($consolidado['custo_ia'], 2, ',', '.') }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Meta (WhatsApp)</dt><dd class="text-gray-800">R$ {{ number_format($consolidado['custo_meta'], 2, ',', '.') }}</dd></div>
                                <div class="flex justify-between"><dt class="text-gray-500">Infraestrutura (rateada)</dt><dd class="text-gray-800">R$ {{ number_format($consolidado['custo_infra_rateado'], 2, ',', '.') }}</dd></div>
                            </dl>
                        </div>
                    </div>

                    <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                        <table class="w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receita</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Custo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lucro bruto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margem</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($fechamentos as $f)
                                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('clientes.financeiro.show', ['cliente' => $f->cliente_id, 'competencia' => $competencia->format('Y-m')]) }}'">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-indigo-600 hover:underline">{{ $f->cliente->nome_empresa }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($f->receitaTotal(), 2, ',', '.') }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($f->custoTotal(), 2, ',', '.') }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm {{ $f->lucro_bruto >= 0 ? 'text-green-700' : 'text-red-700' }}">R$ {{ number_format($f->lucro_bruto, 2, ',', '.') }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $f->margem_percentual !== null ? number_format($f->margem_percentual, 1, ',', '.').'%' : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
