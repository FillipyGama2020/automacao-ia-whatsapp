<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financeiro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @include('financeiro._nav')

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-amber-50 border border-amber-300 rounded-md p-4 text-sm text-amber-800">
                Esses custos são rateados igualmente entre os clientes ativos no fechamento financeiro mensal (Fase 10). Custos inativos ou fora do intervalo de vigência (data início/fim) não entram no rateio.
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <div class="text-xs text-gray-400 uppercase tracking-wider">Total vigente hoje</div>
                <div class="text-lg font-semibold text-gray-800">R$ {{ number_format($totalVigente, 2, ',', '.') }} / mês</div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Adicionar custo</h3>
                <form method="POST" action="{{ route('custos-infraestrutura.store') }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div>
                        <x-input-label for="descricao" value="Descrição" />
                        <x-text-input id="descricao" name="descricao" class="mt-1 block w-48" placeholder="Ex: VPS Contabo" value="{{ old('descricao') }}" />
                        <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="categoria" value="Categoria" />
                        <select id="categoria" name="categoria" class="mt-1 block w-32 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            @foreach (\App\Models\CustoInfraestrutura::categoriaLabels() as $valor => $label)
                                <option value="{{ $valor }}" @selected(old('categoria') === $valor)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('categoria')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="valor_mensal" value="Valor mensal (R$)" />
                        <x-text-input id="valor_mensal" type="number" step="0.01" min="0" name="valor_mensal" class="mt-1 block w-32" value="{{ old('valor_mensal') }}" />
                        <x-input-error :messages="$errors->get('valor_mensal')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="data_inicio" value="Início" />
                        <x-text-input id="data_inicio" type="date" name="data_inicio" class="mt-1 block w-40" value="{{ old('data_inicio', now()->format('Y-m-d')) }}" />
                        <x-input-error :messages="$errors->get('data_inicio')" class="mt-2" />
                    </div>
                    <div class="flex items-center gap-2 pb-2">
                        <input type="hidden" name="ativo" value="0">
                        <input type="checkbox" id="ativo" name="ativo" value="1" checked class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <x-input-label for="ativo" value="Ativo" class="mb-0" />
                    </div>
                    <x-primary-button type="submit">Adicionar</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-100">
                @forelse ($custos as $custo)
                    <div class="flex flex-wrap gap-3 items-end p-4">
                        <form method="POST" action="{{ route('custos-infraestrutura.update', $custo) }}" class="flex flex-wrap gap-3 items-end">
                            @csrf
                            @method('PUT')
                            <div>
                                <x-input-label value="Descrição" />
                                <x-text-input name="descricao" class="mt-1 block w-40" value="{{ $custo->descricao }}" />
                            </div>
                            <div>
                                <x-input-label value="Categoria" />
                                <select name="categoria" class="mt-1 block w-28 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    @foreach (\App\Models\CustoInfraestrutura::categoriaLabels() as $valor => $label)
                                        <option value="{{ $valor }}" @selected($custo->categoria === $valor)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label value="Valor mensal (R$)" />
                                <x-text-input type="number" step="0.01" min="0" name="valor_mensal" class="mt-1 block w-28" value="{{ $custo->valor_mensal }}" />
                            </div>
                            <div>
                                <x-input-label value="Início" />
                                <x-text-input type="date" name="data_inicio" class="mt-1 block w-36" value="{{ $custo->data_inicio->format('Y-m-d') }}" />
                            </div>
                            <div>
                                <x-input-label value="Fim (opcional)" />
                                <x-text-input type="date" name="data_fim" class="mt-1 block w-36" value="{{ $custo->data_fim?->format('Y-m-d') }}" />
                            </div>
                            <div class="flex items-center gap-2 pb-2">
                                <input type="hidden" name="ativo" value="0">
                                <input type="checkbox" name="ativo" value="1" @checked($custo->ativo) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <x-input-label value="Ativo" class="mb-0" />
                            </div>
                            <x-secondary-button type="submit">Salvar</x-secondary-button>
                        </form>
                        <form method="POST" action="{{ route('custos-infraestrutura.destroy', $custo) }}" onsubmit="return confirm('Remover este custo de infraestrutura?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-md text-red-600 hover:bg-red-50 hover:text-red-900">
                                <x-icon name="trash" />
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-8">Nenhum custo de infraestrutura cadastrado ainda.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
