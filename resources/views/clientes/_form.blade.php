@csrf

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div class="sm:col-span-2">
        <x-input-label for="nome_empresa" value="Nome da empresa *" />
        <x-text-input id="nome_empresa" name="nome_empresa" class="mt-1 block w-full" value="{{ old('nome_empresa', $cliente->nome_empresa ?? '') }}" required autofocus />
        <x-input-error :messages="$errors->get('nome_empresa')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="cnpj" value="CNPJ" />
        <x-text-input id="cnpj" name="cnpj" class="mt-1 block w-full" value="{{ old('cnpj', $cliente->cnpj ?? '') }}" />
        <x-input-error :messages="$errors->get('cnpj')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="responsavel" value="Responsável" />
        <x-text-input id="responsavel" name="responsavel" class="mt-1 block w-full" value="{{ old('responsavel', $cliente->responsavel ?? '') }}" />
        <x-input-error :messages="$errors->get('responsavel')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="telefone" value="Telefone" />
        <x-text-input id="telefone" name="telefone" class="mt-1 block w-full" value="{{ old('telefone', $cliente->telefone ?? '') }}" />
        <x-input-error :messages="$errors->get('telefone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" type="email" name="email" class="mt-1 block w-full" value="{{ old('email', $cliente->email ?? '') }}" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="endereco" value="Endereço" />
        <x-text-input id="endereco" name="endereco" class="mt-1 block w-full" value="{{ old('endereco', $cliente->endereco ?? '') }}" />
        <x-input-error :messages="$errors->get('endereco')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="site" value="Site" />
        <x-text-input id="site" name="site" class="mt-1 block w-full" value="{{ old('site', $cliente->site ?? '') }}" />
        <x-input-error :messages="$errors->get('site')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            @foreach (['ativo' => 'Ativo', 'pausado' => 'Pausado', 'arquivado' => 'Arquivado'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $cliente->status ?? 'ativo') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="observacoes" value="Observações" />
        <textarea id="observacoes" name="observacoes" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('observacoes', $cliente->observacoes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('observacoes')" class="mt-2" />
    </div>
</div>

@php
    $planoAtual = $cliente->plano ?? null;
    $planoTipoInicial = old('plano_tipo', $planoAtual?->personalizado ? 'personalizado' : 'catalogo');
@endphp

<div class="mt-8 pt-6 border-t border-gray-200" x-data="{
    planoTipo: '{{ $planoTipoInicial }}',
    persAgentesIlimitado: {{ old('pers_limite_agentes', $planoAtual?->personalizado ? $planoAtual->limite_agentes : null) === null ? 'true' : 'false' }},
    persPermiteAnexos: {{ old('pers_permite_anexos', $planoAtual?->personalizado ? $planoAtual->permite_anexos : true) ? 'true' : 'false' }},
}">
    <h3 class="text-sm font-medium text-gray-700 mb-3">Plano de serviço</h3>

    <div class="flex gap-6 mb-4">
        <label class="inline-flex items-center gap-2">
            <input type="radio" name="plano_tipo" value="catalogo" x-model="planoTipo" class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Plano de catálogo</span>
        </label>
        <label class="inline-flex items-center gap-2">
            <input type="radio" name="plano_tipo" value="personalizado" x-model="planoTipo" class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Personalizado (condições específicas deste cliente)</span>
        </label>
    </div>

    <div x-show="planoTipo === 'catalogo'" x-cloak>
        <select id="plano_id" name="plano_id" class="mt-1 block w-full sm:w-1/2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Nenhum plano</option>
            @foreach ($planos as $plano)
                @continue($plano->personalizado)
                <option value="{{ $plano->id }}" @selected((int) old('plano_id', $cliente->plano_id ?? '') === $plano->id)>
                    {{ $plano->nome }} @if (! $plano->ativo) (inativo) @endif
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('plano_id')" class="mt-2" />
    </div>

    <div x-show="planoTipo === 'personalizado'" x-cloak class="bg-gray-50 rounded-md p-4 space-y-4">
        <p class="text-xs text-gray-500">Condições negociadas só com este cliente — não aparecem no catálogo nem podem ser atribuídas a outro.</p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <x-input-label for="pers_preco_mensal" value="Mensalidade (R$) *" />
                <x-text-input id="pers_preco_mensal" type="number" step="0.01" min="0" name="pers_preco_mensal" class="mt-1 block w-full" value="{{ old('pers_preco_mensal', $planoAtual?->personalizado ? $planoAtual->preco_mensal : '') }}" />
                <x-input-error :messages="$errors->get('pers_preco_mensal')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="pers_limite_conversas_mensais" value="Limite de conversas/mês *" />
                <x-text-input id="pers_limite_conversas_mensais" type="number" min="1" name="pers_limite_conversas_mensais" class="mt-1 block w-full" value="{{ old('pers_limite_conversas_mensais', $planoAtual?->personalizado ? $planoAtual->limite_conversas_mensais : '') }}" />
                <p class="mt-1 text-xs text-gray-500">Obrigatório mesmo aqui — pra não repetir o problema do plano ilimitado antigo.</p>
                <x-input-error :messages="$errors->get('pers_limite_conversas_mensais')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="pers_preco_conversa_excedente" value="Preço por conversa excedente (R$)" />
                <x-text-input id="pers_preco_conversa_excedente" type="number" step="0.0001" min="0" name="pers_preco_conversa_excedente" class="mt-1 block w-full" value="{{ old('pers_preco_conversa_excedente', $planoAtual?->personalizado ? $planoAtual->preco_conversa_excedente : '') }}" />
                <x-input-error :messages="$errors->get('pers_preco_conversa_excedente')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="pers_taxa_implantacao" value="Taxa de implantação (R$)" />
                <x-text-input id="pers_taxa_implantacao" type="number" step="0.01" min="0" name="pers_taxa_implantacao" class="mt-1 block w-full" value="{{ old('pers_taxa_implantacao', $planoAtual?->personalizado ? $planoAtual->taxa_implantacao : '') }}" />
                <x-input-error :messages="$errors->get('pers_taxa_implantacao')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" id="pers_agentes_ilimitado" x-model="persAgentesIlimitado" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <x-input-label for="pers_agentes_ilimitado" value="Agentes de IA ilimitados pra este cliente" class="mb-0" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6" x-show="!persAgentesIlimitado" x-cloak>
            <div>
                <x-input-label for="pers_limite_agentes" value="Limite de agentes de IA" />
                <x-text-input id="pers_limite_agentes" type="number" min="1" name="pers_limite_agentes" x-bind:disabled="persAgentesIlimitado" class="mt-1 block w-full" value="{{ old('pers_limite_agentes', $planoAtual?->personalizado ? $planoAtual->limite_agentes : '') }}" />
                <x-input-error :messages="$errors->get('pers_limite_agentes')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="pers_preco_agente_adicional" value="Preço por agente adicional (R$/mês)" />
                <x-text-input id="pers_preco_agente_adicional" type="number" step="0.01" min="0" name="pers_preco_agente_adicional" x-bind:disabled="persAgentesIlimitado" class="mt-1 block w-full" value="{{ old('pers_preco_agente_adicional', $planoAtual?->personalizado ? $planoAtual->preco_agente_adicional : '') }}" />
                <x-input-error :messages="$errors->get('pers_preco_agente_adicional')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="pers_permite_anexos" value="0">
            <input type="checkbox" id="pers_permite_anexos" name="pers_permite_anexos" value="1" x-model="persPermiteAnexos" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <x-input-label for="pers_permite_anexos" value="Anexos inclusos pra este cliente" class="mb-0" />
        </div>
        <div x-show="!persPermiteAnexos" x-cloak class="sm:w-1/2">
            <x-input-label for="pers_preco_anexos_adicional" value="Preço para habilitar anexos (R$/mês)" />
            <x-text-input id="pers_preco_anexos_adicional" type="number" step="0.01" min="0" name="pers_preco_anexos_adicional" x-bind:disabled="persPermiteAnexos" class="mt-1 block w-full" value="{{ old('pers_preco_anexos_adicional', $planoAtual?->personalizado ? $planoAtual->preco_anexos_adicional : '') }}" />
                <x-input-error :messages="$errors->get('pers_preco_anexos_adicional')" class="mt-2" />
        </div>
    </div>
</div>

<div class="mt-8 pt-6 border-t border-gray-200">
    <h3 class="text-sm font-medium text-gray-700">Mensagens proativas</h3>
    <p class="text-xs text-gray-500 mt-1">Permite que este cliente monte e envie campanhas usando templates aprovados, fora da janela de atendimento reativo. Habilitado manualmente por cliente, independente do plano.</p>

    <label class="mt-3 inline-flex items-center gap-2">
        <input type="hidden" name="mensagens_proativas_habilitado" value="0">
        <input type="checkbox" name="mensagens_proativas_habilitado" value="1" @checked(old('mensagens_proativas_habilitado', $cliente->mensagens_proativas_habilitado ?? false)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <span class="text-sm text-gray-700">Habilitar mensagens proativas para este cliente</span>
    </label>
</div>

<div class="mt-8 pt-6 border-t border-gray-200">
    <h3 class="text-sm font-medium text-gray-700">Leads no portal do cliente</h3>
    <p class="text-xs text-gray-500 mt-1">Permite que este cliente veja a lista de leads dele no próprio portal (só leitura — cadastro e edição continuam só no admin). Habilitado manualmente por cliente, independente do plano.</p>

    <label class="mt-3 inline-flex items-center gap-2">
        <input type="hidden" name="leads_portal_habilitado" value="0">
        <input type="checkbox" name="leads_portal_habilitado" value="1" @checked(old('leads_portal_habilitado', $cliente->leads_portal_habilitado ?? false)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <span class="text-sm text-gray-700">Habilitar acesso a leads no portal para este cliente</span>
    </label>
</div>

@php($portalUser = $portalUser ?? null)

<div class="mt-8 pt-6 border-t border-gray-200">
    <h3 class="text-sm font-medium text-gray-700">Acesso ao portal do cliente</h3>

    @if ($portalUser)
        <p class="text-xs text-gray-500 mt-1">
            Login atual: <strong>{{ $portalUser->email }}</strong> — se você mudar o e-mail acima, o login é atualizado junto.
        </p>

        <label class="mt-3 inline-flex items-center gap-2">
            <input type="checkbox" name="reenviar_acesso_portal" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Enviar e-mail para o cliente definir uma nova senha</span>
        </label>
    @else
        <p class="text-xs text-gray-500 mt-1">
            {{ isset($cliente) ? 'Este cliente ainda não tem login no portal.' : '' }}
            Opcional. Se marcar, enviamos um e-mail para o endereço informado acima com um link para o próprio cliente definir a senha de acesso.
        </p>

        <label class="mt-3 inline-flex items-center gap-2">
            <input type="checkbox" name="criar_acesso" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Criar acesso ao portal para este cliente agora</span>
        </label>
    @endif
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ __('Salvar') }}</x-primary-button>
    <a href="{{ route('clientes.index') }}" class="text-sm text-gray-600 underline">Cancelar</a>
</div>
