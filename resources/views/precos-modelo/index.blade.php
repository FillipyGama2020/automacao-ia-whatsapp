<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Preços por modelo de IA') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

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

            <div class="bg-amber-50 border border-amber-300 rounded-md p-4 text-sm text-amber-800">
                Os preços abaixo são em <strong>US$ por 1000 tokens</strong>, exatamente como a OpenAI cobra — confira sempre o valor atual em <a href="https://openai.com/api/pricing" target="_blank" class="underline">openai.com/api/pricing</a> antes de usar para cobrar clientes, já que a tabela deles muda de vez em quando. A conversão para reais usa a cotação abaixo, atualizada automaticamente todo dia.
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 flex items-center justify-between">
                <div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider">Cotação do dólar (venda, PTAX/Banco Central)</div>
                    <div class="text-lg font-semibold text-gray-800">
                        @if ($cotacaoDolar > 0)
                            R$ {{ number_format($cotacaoDolar, 4, ',', '.') }}
                        @else
                            <span class="text-red-600 text-sm font-normal">Ainda não configurada — clique em atualizar</span>
                        @endif
                    </div>
                    @if ($cotacaoAtualizadaEm)
                        <div class="text-xs text-gray-400 mt-1">Atualizada em {{ \Illuminate\Support\Carbon::parse($cotacaoAtualizadaEm)->format('d/m/Y H:i') }}</div>
                    @endif
                </div>
                <form method="POST" action="{{ route('precos-modelo.atualizar-cotacao') }}">
                    @csrf
                    <x-secondary-button type="submit">Atualizar agora</x-secondary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Adicionar modelo</h3>
                <form method="POST" action="{{ route('precos-modelo.store') }}" class="flex flex-wrap gap-3 items-end">
                    @csrf
                    <div>
                        <x-input-label for="modelo" value="Nome do modelo" />
                        <x-text-input id="modelo" name="modelo" class="mt-1 block w-48" placeholder="Ex: gpt-4o-mini" value="{{ old('modelo') }}" />
                        <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="preco_prompt_usd_por_mil" value="US$ / 1000 tokens (prompt)" />
                        <x-text-input id="preco_prompt_usd_por_mil" type="number" step="0.000001" min="0" name="preco_prompt_usd_por_mil" class="mt-1 block w-44" value="{{ old('preco_prompt_usd_por_mil') }}" />
                        <x-input-error :messages="$errors->get('preco_prompt_usd_por_mil')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="preco_resposta_usd_por_mil" value="US$ / 1000 tokens (resposta)" />
                        <x-text-input id="preco_resposta_usd_por_mil" type="number" step="0.000001" min="0" name="preco_resposta_usd_por_mil" class="mt-1 block w-44" value="{{ old('preco_resposta_usd_por_mil') }}" />
                        <x-input-error :messages="$errors->get('preco_resposta_usd_por_mil')" class="mt-2" />
                    </div>
                    <x-primary-button type="submit">Adicionar</x-primary-button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-100">
                @forelse ($precos as $preco)
                    <div class="flex flex-wrap gap-3 items-end p-4">
                        <form method="POST" action="{{ route('precos-modelo.update', $preco) }}" class="flex flex-wrap gap-3 items-end">
                            @csrf
                            @method('PUT')
                            <div>
                                <x-input-label value="Modelo" />
                                <div class="mt-1 py-2 text-sm font-medium text-gray-900 w-40">{{ $preco->modelo }}</div>
                                <input type="hidden" name="modelo" value="{{ $preco->modelo }}">
                            </div>
                            <div>
                                <x-input-label for="preco_prompt_usd_por_mil_{{ $preco->id }}" value="US$ / 1000 (prompt)" />
                                <x-text-input id="preco_prompt_usd_por_mil_{{ $preco->id }}" type="number" step="0.000001" min="0" name="preco_prompt_usd_por_mil" class="mt-1 block w-36" value="{{ $preco->preco_prompt_usd_por_mil }}" />
                            </div>
                            <div>
                                <x-input-label for="preco_resposta_usd_por_mil_{{ $preco->id }}" value="US$ / 1000 (resposta)" />
                                <x-text-input id="preco_resposta_usd_por_mil_{{ $preco->id }}" type="number" step="0.000001" min="0" name="preco_resposta_usd_por_mil" class="mt-1 block w-36" value="{{ $preco->preco_resposta_usd_por_mil }}" />
                            </div>
                            <div class="text-xs text-gray-400 pb-2">
                                @if ($cotacaoDolar > 0)
                                    ≈ R$ {{ number_format($preco->preco_prompt_usd_por_mil * $cotacaoDolar, 6, ',', '.') }} / R$ {{ number_format($preco->preco_resposta_usd_por_mil * $cotacaoDolar, 6, ',', '.') }}
                                @endif
                            </div>
                            <x-secondary-button type="submit">Salvar</x-secondary-button>
                        </form>
                        <form method="POST" action="{{ route('precos-modelo.destroy', $preco) }}" onsubmit="return confirm('Remover o preço deste modelo? Conversas que usarem esse modelo passam a ter custo R$ 0 até ser recadastrado.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-md text-red-600 hover:bg-red-50 hover:text-red-900">
                                <x-icon name="trash" />
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center py-8">Nenhum modelo cadastrado ainda.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
