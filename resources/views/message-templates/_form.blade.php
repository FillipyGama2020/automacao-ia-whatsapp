@csrf

<div x-data="{ variaveis: @js(old('variaveis', $template->variaveis ?? [])) }" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="cliente_id" value="Cliente *" />
        <select id="cliente_id" name="cliente_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            <option value="">Selecione...</option>
            @foreach ($clientes as $cliente)
                <option value="{{ $cliente->id }}" @selected((int) old('cliente_id', $template->cliente_id ?? '') === $cliente->id)>{{ $cliente->nome_empresa }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">O template é submetido à conta do WhatsApp deste cliente especificamente — pra usar o mesmo texto em outro cliente, crie um template separado.</p>
        <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="nome" value="Nome técnico *" />
        <x-text-input id="nome" name="nome" class="mt-1 block w-full font-mono" value="{{ old('nome', $template->nome ?? '') }}" placeholder="ex: lembrete_renovacao" required />
        <p class="mt-1 text-xs text-gray-500">Só letras minúsculas, números e underscore — é o identificador usado na API da Meta.</p>
        <x-input-error :messages="$errors->get('nome')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="idioma" value="Idioma *" />
        <select id="idioma" name="idioma" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="pt_BR" @selected(old('idioma', $template->idioma ?? 'pt_BR') === 'pt_BR')>Português (Brasil)</option>
            <option value="en_US" @selected(old('idioma', $template->idioma ?? '') === 'en_US')>Inglês (EUA)</option>
            <option value="es_ES" @selected(old('idioma', $template->idioma ?? '') === 'es_ES')>Espanhol</option>
        </select>
        <x-input-error :messages="$errors->get('idioma')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="categoria" value="Categoria *" />
        <select id="categoria" name="categoria" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            @foreach (\App\Models\MessageTemplate::categoriaLabels() as $valor => $label)
                <option value="{{ $valor }}" @selected(old('categoria', $template->categoria ?? '') === $valor)>{{ $label }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-500">Define o preço do pacote — Marketing custa bem mais caro pra Meta que Utilidade/Autenticação.</p>
        <x-input-error :messages="$errors->get('categoria')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="corpo" value="Texto do template *" />
        <textarea id="corpo" name="corpo" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Olá @{{1}}, seu produto @{{2}} está quase vencendo...">{{ old('corpo', $template->corpo ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Use @{{1}}, @{{2}}... para as variáveis que serão preenchidas na hora do envio.</p>
        <x-input-error :messages="$errors->get('corpo')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <div class="flex items-center justify-between mb-2">
            <x-input-label value="O que cada variável representa" class="mb-0" />
            <button type="button" @click="variaveis.push('')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Adicionar variável</button>
        </div>
        <p class="text-xs text-gray-400 mb-2">Só documentação — ajuda a lembrar o que @{{1}}, @{{2}}... significam na hora de montar um envio.</p>
        <p x-show="variaveis.length === 0" class="text-sm text-gray-400">Nenhuma variável descrita.</p>
        <template x-for="(variavel, index) in variaveis" :key="index">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-xs text-gray-400 font-mono" x-text="'@{{' + (index + 1) + '}}'"></span>
                <input type="text" :name="`variaveis[${index}]`" x-model="variaveis[index]" placeholder="Ex: Nome do lead" class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                <button type="button" @click="variaveis.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">Remover</button>
            </div>
        </template>
    </div>
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ __('Salvar como rascunho') }}</x-primary-button>
    <a href="{{ route('message-templates.index') }}" class="text-sm text-gray-600 underline">Cancelar</a>
</div>
