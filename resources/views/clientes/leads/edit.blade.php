<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.leads.index', $cliente) }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar lead — ') }}{{ $lead->nome ?? $lead->telefone }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('clientes.leads.update', [$cliente, $lead]) }}">
                    @method('PUT')
                    @include('clientes.leads._form')
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
