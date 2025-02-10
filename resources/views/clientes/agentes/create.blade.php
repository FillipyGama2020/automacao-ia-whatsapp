<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.agentes.index', $cliente) }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Novo agente — ') }}{{ $cliente->nome_empresa }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('clientes.agentes.store', $cliente) }}" enctype="multipart/form-data">
                    @include('clientes.agentes._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
