<x-portal-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('portal.contatos-bloqueados.index') }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar contato bloqueado') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('portal.contatos-bloqueados.update', $contato) }}">
                    @method('PUT')
                    @include('portal.contatos-bloqueados._form')
                </form>
            </div>
        </div>
    </div>
</x-portal-layout>
