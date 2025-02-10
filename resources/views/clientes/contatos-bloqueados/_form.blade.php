@csrf

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="telefone" value="Telefone *" />
        <x-text-input id="telefone" name="telefone" class="mt-1 block w-full" value="{{ old('telefone', $contato->telefone ?? '') }}" placeholder="+55 11 99999-9999" required autofocus />
        <x-input-error :messages="$errors->get('telefone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nome" value="Nome (opcional)" />
        <x-text-input id="nome" name="nome" class="mt-1 block w-full" value="{{ old('nome', $contato->nome ?? '') }}" placeholder="Ex: Mãe, Amigo do trabalho" />
        <x-input-error :messages="$errors->get('nome')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="observacoes" value="Observações" />
        <textarea id="observacoes" name="observacoes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('observacoes', $contato->observacoes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('observacoes')" class="mt-2" />
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ __('Salvar') }}</x-primary-button>
    <a href="{{ route('clientes.contatos-bloqueados.index', $cliente) }}" class="text-sm text-gray-600 underline">Cancelar</a>
</div>
