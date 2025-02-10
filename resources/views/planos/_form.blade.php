@php
    $recursosOld = old('recursos');
    if ($recursosOld) {
        $recursosData = array_values($recursosOld);
    } elseif ($plano->exists ?? false) {
        $recursosData = $plano->recursos->pluck('descricao')->values()->all();
    } else {
        $recursosData = [];
    }
@endphp

@csrf

<div x-data="{
    conversasIlimitado: {{ old('limite_conversas_mensais', $plano->limite_conversas_mensais ?? null) === null ? 'true' : 'false' }},
    agentesIlimitado: {{ old('limite_agentes', $plano->limite_agentes ?? null) === null ? 'true' : 'false' }},
    permiteAnexos: {{ old('permite_anexos', $plano->permite_anexos ?? true) ? 'true' : 'false' }},
    recursos: @js($recursosData),
}">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="sm:col-span-2">
            <x-input-label for="nome" value="Nome do plano *" />
            <x-text-input id="nome" name="nome" class="mt-1 block w-full" value="{{ old('nome', $plano->nome ?? '') }}" required autofocus />
            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
        </div>

        <div class="sm:col-span-2">
            <x-input-label for="descricao" value="Descrição (indicado para)" />
            <textarea id="descricao" name="descricao" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('descricao', $plano->descricao ?? '') }}</textarea>
            <x-input-error :messages="$errors->get('descricao')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="ordem" value="Ordem de exibição" />
            <x-text-input id="ordem" type="number" min="0" name="ordem" class="mt-1 block w-full" value="{{ old('ordem', $plano->ordem ?? 0) }}" />
            <x-input-error :messages="$errors->get('ordem')" class="mt-2" />
        </div>

        <div class="flex items-center gap-2 self-end pb-2">
            <input type="hidden" name="ativo" value="0">
            <input type="checkbox" id="ativo" name="ativo" value="1" @checked(old('ativo', $plano->ativo ?? true)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <x-input-label for="ativo" value="Plano ativo (disponível para atribuir a clientes)" class="mb-0" />
        </div>
    </div>

    <hr class="my-6 border-gray-200">

    <h3 class="text-sm font-semibold text-gray-700 mb-4">Preços</h3>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div>
            <x-input-label for="preco_mensal" value="Preço mensal (R$) *" />
            <x-text-input id="preco_mensal" type="number" step="0.01" min="0" name="preco_mensal" class="mt-1 block w-full" value="{{ old('preco_mensal', $plano->preco_mensal ?? '') }}" required />
            <x-input-error :messages="$errors->get('preco_mensal')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="preco_anual" value="Preço anual (R$)" />
            <x-text-input id="preco_anual" type="number" step="0.01" min="0" name="preco_anual" class="mt-1 block w-full" value="{{ old('preco_anual', $plano->preco_anual ?? '') }}" />
            <p class="mt-1 text-xs text-gray-500">Deixe em branco se o plano só for oferecido mensalmente.</p>
            <x-input-error :messages="$errors->get('preco_anual')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="taxa_implantacao" value="Taxa de implantação (R$)" />
            <x-text-input id="taxa_implantacao" type="number" step="0.01" min="0" name="taxa_implantacao" class="mt-1 block w-full" value="{{ old('taxa_implantacao', $plano->taxa_implantacao ?? '') }}" />
            <p class="mt-1 text-xs text-gray-500">Pagamento único, cobrado na contratação.</p>
            <x-input-error :messages="$errors->get('taxa_implantacao')" class="mt-2" />
        </div>
    </div>

    <hr class="my-6 border-gray-200">

    <h3 class="text-sm font-semibold text-gray-700 mb-4">Limite de conversas mensais</h3>
    <div class="flex items-center gap-2 mb-4">
        <input type="checkbox" id="conversas_ilimitado" x-model="conversasIlimitado" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <x-input-label for="conversas_ilimitado" value="Conversas ilimitadas neste plano" class="mb-0" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div x-show="!conversasIlimitado" x-cloak>
            <x-input-label for="limite_conversas_mensais" value="Limite de conversas por mês" />
            <x-text-input id="limite_conversas_mensais" type="number" min="1" name="limite_conversas_mensais" x-bind:disabled="conversasIlimitado" class="mt-1 block w-full" value="{{ old('limite_conversas_mensais', $plano->limite_conversas_mensais ?? '') }}" />
            <x-input-error :messages="$errors->get('limite_conversas_mensais')" class="mt-2" />
        </div>

        <div x-show="!conversasIlimitado" x-cloak>
            <x-input-label for="preco_conversa_excedente" value="Preço por conversa excedente (R$)" />
            <x-text-input id="preco_conversa_excedente" type="number" step="0.0001" min="0" name="preco_conversa_excedente" x-bind:disabled="conversasIlimitado" class="mt-1 block w-full" value="{{ old('preco_conversa_excedente', $plano->preco_conversa_excedente ?? '') }}" />
            <p class="mt-1 text-xs text-gray-500">Valor de referência — a cobrança automática ainda não existe, fica pra área financeira.</p>
            <x-input-error :messages="$errors->get('preco_conversa_excedente')" class="mt-2" />
        </div>
    </div>

    <hr class="my-6 border-gray-200">

    <h3 class="text-sm font-semibold text-gray-700 mb-4">Limite de agentes de IA</h3>
    <div class="flex items-center gap-2 mb-4">
        <input type="checkbox" id="agentes_ilimitado" x-model="agentesIlimitado" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <x-input-label for="agentes_ilimitado" value="Agentes ilimitados neste plano" class="mb-0" />
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div x-show="!agentesIlimitado" x-cloak>
            <x-input-label for="limite_agentes" value="Limite de agentes de IA" />
            <x-text-input id="limite_agentes" type="number" min="1" name="limite_agentes" x-bind:disabled="agentesIlimitado" class="mt-1 block w-full" value="{{ old('limite_agentes', $plano->limite_agentes ?? '') }}" />
            <x-input-error :messages="$errors->get('limite_agentes')" class="mt-2" />
        </div>

        <div x-show="!agentesIlimitado" x-cloak>
            <x-input-label for="preco_agente_adicional" value="Preço por agente adicional (R$/mês)" />
            <x-text-input id="preco_agente_adicional" type="number" step="0.01" min="0" name="preco_agente_adicional" x-bind:disabled="agentesIlimitado" class="mt-1 block w-full" value="{{ old('preco_agente_adicional', $plano->preco_agente_adicional ?? '') }}" />
            <p class="mt-1 text-xs text-gray-500">Valor de referência para upsell — sem cobrança automática ainda.</p>
            <x-input-error :messages="$errors->get('preco_agente_adicional')" class="mt-2" />
        </div>
    </div>

    <hr class="my-6 border-gray-200">

    <h3 class="text-sm font-semibold text-gray-700 mb-4">Anexos</h3>
    <div class="flex items-center gap-2 mb-4">
        <input type="hidden" name="permite_anexos" value="0">
        <input type="checkbox" id="permite_anexos" name="permite_anexos" value="1" x-model="permiteAnexos" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
        <x-input-label for="permite_anexos" value="Anexos inclusos neste plano" class="mb-0" />
    </div>
    <div x-show="!permiteAnexos" x-cloak class="sm:w-1/2">
        <x-input-label for="preco_anexos_adicional" value="Preço para habilitar anexos (R$/mês)" />
        <x-text-input id="preco_anexos_adicional" type="number" step="0.01" min="0" name="preco_anexos_adicional" x-bind:disabled="permiteAnexos" class="mt-1 block w-full" value="{{ old('preco_anexos_adicional', $plano->preco_anexos_adicional ?? '') }}" />
        <p class="mt-1 text-xs text-gray-500">Valor de referência — processar anexo custa mais IA que texto puro. Sem cobrança automática ainda.</p>
        <x-input-error :messages="$errors->get('preco_anexos_adicional')" class="mt-2" />
    </div>

    <hr class="my-6 border-gray-200">

    <h3 class="text-sm font-semibold text-gray-700 mb-4">Recursos inclusos</h3>
    <div class="flex items-center justify-between mb-3">
        <p class="text-sm text-gray-400">Ex: "Atendimento automático 24h", "Respostas inteligentes", "Suporte padrão".</p>
        <button type="button" @click="recursos.push('')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium whitespace-nowrap ml-4">+ Adicionar recurso</button>
    </div>
    <p x-show="recursos.length === 0" class="text-sm text-gray-400">Nenhum recurso cadastrado.</p>
    <template x-for="(recurso, index) in recursos" :key="index">
        <div class="flex items-center gap-3 mb-2">
            <input type="text" :name="`recursos[${index}]`" x-model="recursos[index]" maxlength="255" class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
            <button type="button" @click="recursos.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">Remover</button>
        </div>
    </template>
</div>

<div class="flex items-center gap-4 mt-8">
    <x-primary-button>{{ __('Salvar') }}</x-primary-button>
    <a href="{{ route('planos.index') }}" class="text-sm text-gray-600 underline">Cancelar</a>
</div>
