<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financeiro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @include('financeiro._nav')

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-amber-50 border border-amber-300 rounded-md p-4 text-sm text-amber-800">
                Os números de um mês já fechado ficam <strong>congelados</strong> — não recalculam sozinhos se você editar preço de plano ou custo de infraestrutura depois. Marque "Recalcular" só se precisar corrigir um fechamento já feito.
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Fechar competência</h3>
                <form method="POST" action="{{ route('financeiro.fechamentos.store') }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div>
                        <x-input-label for="competencia" value="Mês (competência)" />
                        <input type="month" id="competencia" name="competencia" value="{{ now()->subMonthNoOverflow()->format('Y-m') }}" class="mt-1 block border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <x-input-error :messages="$errors->get('competencia')" class="mt-2" />
                    </div>
                    <div class="flex items-center gap-2 pb-2">
                        <input type="checkbox" id="forcar" name="forcar" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                        <x-input-label for="forcar" value="Recalcular (sobrescreve fechamento já existente)" class="mb-0" />
                    </div>
                    <x-primary-button type="submit">Fechar mês</x-primary-button>
                </form>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Competência</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receita</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Custo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lucro bruto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margem</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fechado por</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($fechamentos as $f)
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $f->competencia->format('m/Y') }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">{{ $f->cliente->nome_empresa }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($f->receitaTotal(), 2, ',', '.') }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($f->custoTotal(), 2, ',', '.') }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm {{ $f->lucro_bruto >= 0 ? 'text-green-700' : 'text-red-700' }}">R$ {{ number_format($f->lucro_bruto, 2, ',', '.') }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $f->margem_percentual !== null ? number_format($f->margem_percentual, 1, ',', '.').'%' : '—' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500">{{ $f->fechadoPor->name ?? 'Sistema (agendado)' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Nenhum fechamento realizado ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $fechamentos->links() }}
        </div>
    </div>
</x-app-layout>
