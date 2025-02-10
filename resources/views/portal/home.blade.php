<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Minha Empresa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Dados da empresa</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 uppercase tracking-wider">Empresa</dt>
                        <dd class="text-gray-800">{{ $cliente->nome_empresa }}</dd>
                    </div>
                    @if ($cliente->cnpj)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wider">CNPJ</dt>
                            <dd class="text-gray-800">{{ $cliente->cnpj }}</dd>
                        </div>
                    @endif
                    @if ($cliente->responsavel)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wider">Responsável</dt>
                            <dd class="text-gray-800">{{ $cliente->responsavel }}</dd>
                        </div>
                    @endif
                    @if ($cliente->telefone)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wider">Telefone</dt>
                            <dd class="text-gray-800">{{ $cliente->telefone }}</dd>
                        </div>
                    @endif
                    @if ($cliente->email)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wider">Email</dt>
                            <dd class="text-gray-800">{{ $cliente->email }}</dd>
                        </div>
                    @endif
                    @if ($cliente->endereco)
                        <div>
                            <dt class="text-xs text-gray-400 uppercase tracking-wider">Endereço</dt>
                            <dd class="text-gray-800">{{ $cliente->endereco }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Plano contratado</h3>
                @if (! $cliente->plano)
                    <p class="text-sm text-gray-500">Nenhum plano associado no momento.</p>
                @else
                    <div class="mb-4">
                        <div class="text-lg font-semibold text-gray-800">{{ $cliente->plano->nome }}</div>
                        <div class="text-sm text-gray-500">R$ {{ number_format($cliente->plano->preco_mensal, 2, ',', '.') }} / mês</div>
                    </div>
                    @if ($cliente->plano->recursos->isNotEmpty())
                        <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                            @foreach ($cliente->plano->recursos as $recurso)
                                <li>{{ $recurso->descricao }}</li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-portal-layout>
