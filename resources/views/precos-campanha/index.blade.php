<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Preços de Mensagens Proativas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-amber-50 border border-amber-300 rounded-md p-4 text-sm text-amber-800">
                Preço fixo por lead, único e igual para todos os clientes — o valor cobrado numa campanha é
                <strong>quantidade de leads × preço por lead da categoria do template</strong>. Varia por categoria
                porque o custo real cobrado pela Meta entre Marketing/Utilidade/Autenticação é bem diferente.
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg divide-y divide-gray-100">
                @foreach ($precos as $preco)
                    <form method="POST" action="{{ route('precos-campanha.update', $preco) }}" class="flex flex-wrap gap-3 items-end p-4">
                        @csrf
                        @method('PUT')
                        <div class="flex-1">
                            <x-input-label value="Categoria" />
                            <div class="mt-1 py-2 text-sm font-medium text-gray-900">{{ \App\Models\MessageTemplate::categoriaLabels()[$preco->categoria] }}</div>
                        </div>
                        <div>
                            <x-input-label for="preco_por_lead_{{ $preco->id }}" value="Preço por lead (R$)" />
                            <x-text-input id="preco_por_lead_{{ $preco->id }}" type="number" step="0.01" min="0" name="preco_por_lead" class="mt-1 block w-40" value="{{ $preco->preco_por_lead }}" />
                        </div>
                        <x-secondary-button type="submit">Salvar</x-secondary-button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
