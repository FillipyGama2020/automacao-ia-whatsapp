<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Total de clientes</div>
                    <div class="text-3xl font-semibold text-gray-900">{{ $totalClientes }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Ativos</div>
                    <div class="text-3xl font-semibold text-green-600">{{ $clientesAtivos }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Pausados</div>
                    <div class="text-3xl font-semibold text-yellow-600">{{ $clientesPausados }}</div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500">Arquivados</div>
                    <div class="text-3xl font-semibold text-gray-500">{{ $clientesArquivados }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    Bem-vindo, {{ Auth::user()->name }}. Use o menu "Clientes" para cadastrar e gerenciar os clientes do chatbot de IA.
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
