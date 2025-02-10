<x-portal-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('portal.suporte.index') }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Abrir chamado') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('portal.suporte.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="assunto" value="Assunto" />
                        <x-text-input id="assunto" name="assunto" class="mt-1 block w-full" value="{{ old('assunto') }}" required autofocus />
                        <x-input-error :messages="$errors->get('assunto')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="mensagem" value="Mensagem" />
                        <textarea id="mensagem" name="mensagem" rows="6" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>{{ old('mensagem') }}</textarea>
                        <x-input-error :messages="$errors->get('mensagem')" class="mt-2" />
                    </div>
                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('Enviar') }}</x-primary-button>
                        <a href="{{ route('portal.suporte.index') }}" class="text-sm text-gray-600 underline">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-portal-layout>
