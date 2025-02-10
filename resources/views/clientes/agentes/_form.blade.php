@csrf

@php
    $erroTab = 'geral';
    if ($errors->hasAny(['temperatura', 'nome_ia', 'papel', 'tom_voz', 'emojis', 'tamanho_respostas', 'forma_tratamento', 'forma_tratamento_personalizada']) && ! $errors->hasAny(['nome', 'idioma', 'timezone', 'avatar', 'cor'])) {
        $erroTab = 'personalidade';
    } elseif ($errors->hasAny(['prompt_principal', 'prompt_complementar', 'prompt_horario_fechado', 'prompt_transferencia_humano', 'prompt_despedida', 'prompt_nao_sei_responder', 'prompt_vendas', 'prompt_suporte'])) {
        $erroTab = 'prompt';
    } elseif ($errors->hasAny(['mensagem_fora_horario', 'transferencia_automatica_fora_horario']) || collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'horarios.') || str_starts_with($k, 'feriados.'))) {
        $erroTab = 'horarios';
    } elseif ($errors->hasAny(['modelo', 'top_p', 'frequency_penalty', 'presence_penalty', 'max_tokens', 'timeout', 'modelo_fallback'])) {
        $erroTab = 'modelo';
    } elseif ($errors->hasAny(['limite_mensagens_conversa', 'limite_tokens_conversa', 'prompt_limite_atingido', 'limite_mensagens_minuto', 'limite_mensagens_dia', 'retomada_automatica_minutos', 'prompt_retomada_automatica'])) {
        $erroTab = 'limites';
    } elseif ($errors->hasAny(['permitir_atendimento_humano', 'mascarar_cpf', 'mascarar_cartao', 'permitir_anexos', 'tipos_anexos_permitidos'])) {
        $erroTab = 'seguranca';
    } elseif ($errors->hasAny(['memoria_habilitada', 'memoria_dias_lembrar', 'memoria_resumo_automatico', 'memoria_salvar_preferencias', 'memoria_salvar_nome', 'memoria_salvar_endereco'])) {
        $erroTab = 'memoria';
    } elseif (collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'regras.'))) {
        $erroTab = 'regras';
    } elseif ($errors->hasAny(['ferramentas_habilitadas'])) {
        $erroTab = 'ferramentas';
    } elseif (collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'faqs.') || str_starts_with($k, 'produtos.') || str_starts_with($k, 'politicas.') || str_starts_with($k, 'documentos.'))) {
        $erroTab = 'conhecimento';
    }
@endphp

<div x-data="{ tab: '{{ $erroTab }}' }">
    <div class="border-b border-gray-200 mb-6 overflow-x-auto">
        <nav class="flex gap-4 min-w-max text-sm font-medium">
            <button type="button" @click="tab = 'geral'" :class="tab === 'geral' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Informações Gerais</button>
            <button type="button" @click="tab = 'personalidade'" :class="tab === 'personalidade' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Personalidade</button>
            <button type="button" @click="tab = 'prompt'" :class="tab === 'prompt' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Prompt</button>
            <button type="button" @click="tab = 'horarios'" :class="tab === 'horarios' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Horários</button>
            <button type="button" @click="tab = 'conhecimento'" :class="tab === 'conhecimento' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Conhecimento</button>
            <button type="button" @click="tab = 'ferramentas'" :class="tab === 'ferramentas' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Ferramentas</button>
            <button type="button" @click="tab = 'regras'" :class="tab === 'regras' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Regras de Negócio</button>
            <button type="button" @click="tab = 'memoria'" :class="tab === 'memoria' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Memória</button>
            <button type="button" @click="tab = 'modelo'" :class="tab === 'modelo' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Modelo da IA</button>
            <button type="button" @click="tab = 'limites'" :class="tab === 'limites' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Limites</button>
            <button type="button" @click="tab = 'seguranca'" :class="tab === 'seguranca' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Segurança</button>
        </nav>
    </div>

    <div x-show="tab === 'geral'" x-cloak>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2 flex items-center gap-4">
                @if ($agente->avatar ?? null)
                    <img src="{{ asset('storage/' . $agente->avatar) }}" class="w-16 h-16 rounded-full object-cover bg-gray-100 border border-gray-200" alt="Avatar do agente">
                @else
                    <div class="w-16 h-16 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center">
                        <x-icon name="headset" class="w-6 h-6 text-gray-300" />
                    </div>
                @endif
                <div class="flex-1">
                    <x-input-label for="avatar" value="Foto (avatar)" />
                    <input id="avatar" type="file" name="avatar" accept="image/*" class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG ou GIF, até 8MB.</p>
                    <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                </div>
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="nome" value="Nome do agente *" />
                <x-text-input id="nome" name="nome" class="mt-1 block w-full" value="{{ old('nome', $agente->nome ?? '') }}" autofocus />
                <x-input-error :messages="$errors->get('nome')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="objetivo" value="Objetivo" />
                <x-text-input id="objetivo" name="objetivo" class="mt-1 block w-full" value="{{ old('objetivo', $agente->objetivo ?? '') }}" placeholder="Ex: Atender dúvidas sobre produtos e qualificar leads" />
                <x-input-error :messages="$errors->get('objetivo')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="descricao_interna" value="Descrição interna" />
                <textarea id="descricao_interna" name="descricao_interna" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Anotação sua sobre este agente — não é vista pelo cliente final">{{ old('descricao_interna', $agente->descricao_interna ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('descricao_interna')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="departamento" value="Departamento" />
                <x-text-input id="departamento" name="departamento" class="mt-1 block w-full" value="{{ old('departamento', $agente->departamento ?? '') }}" placeholder="Ex: Vendas, Suporte, Financeiro" />
                <x-input-error :messages="$errors->get('departamento')" class="mt-2" />
            </div>

            @if (($integracoes ?? collect())->count() >= 2)
                <div class="sm:col-span-2">
                    <x-input-label for="whatsapp_integracao_id" value="Qual número esse agente atende? *" />
                    <select id="whatsapp_integracao_id" name="whatsapp_integracao_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="">Selecione...</option>
                        @foreach ($integracoes as $integracao)
                            <option value="{{ $integracao->id }}" @selected(old('whatsapp_integracao_id', $agente->whatsapp_integracao_id ?? '') == $integracao->id)>
                                {{ $integracao->phone_number_id ?? 'Conexão pendente #'.$integracao->id }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Este cliente tem mais de um número conectado — escolha qual número este agente atende.</p>
                    <x-input-error :messages="$errors->get('whatsapp_integracao_id')" class="mt-2" />
                </div>
            @endif

            <div>
                <x-input-label for="cor" value="Cor do agente" />
                <div class="mt-1 flex items-center gap-2">
                    <input id="cor" type="color" name="cor" value="{{ old('cor', $agente->cor ?? '#6366f1') }}" class="h-9 w-14 rounded border border-gray-300 cursor-pointer">
                    <span class="text-xs text-gray-400">Usada para identificar o agente no painel</span>
                </div>
                <x-input-error :messages="$errors->get('cor')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="idioma" value="Idioma *" />
                <select id="idioma" name="idioma" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach (['pt-BR' => 'Português (Brasil)', 'en-US' => 'Inglês (EUA)', 'es-ES' => 'Espanhol'] as $valor => $label)
                        <option value="{{ $valor }}" @selected(old('idioma', $agente->idioma ?? 'pt-BR') === $valor)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('idioma')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="timezone" value="Timezone *" />
                <select id="timezone" name="timezone" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach ([
                        'America/Sao_Paulo' => 'Brasília (UTC-3)',
                        'America/Manaus' => 'Manaus (UTC-4)',
                        'America/Belem' => 'Belém (UTC-3)',
                        'America/Rio_Branco' => 'Rio Branco (UTC-5)',
                        'America/Noronha' => 'Fernando de Noronha (UTC-2)',
                    ] as $valor => $label)
                        <option value="{{ $valor }}" @selected(old('timezone', $agente->timezone ?? 'America/Sao_Paulo') === $valor)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <input type="hidden" name="ativo" value="0">
                <input type="checkbox" id="ativo" name="ativo" value="1" @checked(old('ativo', $agente->ativo ?? true)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <x-input-label for="ativo" value="Agente ativo" class="mb-0" />
            </div>
        </div>
    </div>

    <div x-show="tab === 'personalidade'" x-cloak x-data="{ tratamento: '{{ old('forma_tratamento', $agente->forma_tratamento ?? 'voce') }}' }">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <x-input-label for="nome_ia" value="Nome que a IA utilizará" />
                <x-text-input id="nome_ia" name="nome_ia" class="mt-1 block w-full" value="{{ old('nome_ia', $agente->nome_ia ?? '') }}" placeholder='Ex: "Sou a Ana, assistente virtual da Clínica XYZ"' />
                <x-input-error :messages="$errors->get('nome_ia')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="papel" value="Papel" />
                <select id="papel" name="papel" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione...</option>
                    @foreach (['Secretária', 'Vendedora', 'Suporte', 'Recepcionista', 'Pós-venda', 'Financeiro', 'RH'] as $opcao)
                        <option value="{{ $opcao }}" @selected(old('papel', $agente->papel ?? '') === $opcao)>{{ $opcao }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('papel')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tom_voz" value="Tom de voz" />
                <select id="tom_voz" name="tom_voz" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    <option value="">Selecione...</option>
                    @foreach (['Formal', 'Amigável', 'Profissional', 'Jovem', 'Divertido', 'Premium', 'Técnico'] as $opcao)
                        <option value="{{ $opcao }}" @selected(old('tom_voz', $agente->tom_voz ?? '') === $opcao)>{{ $opcao }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('tom_voz')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="temperatura" value="Nível de criatividade *" />
                <input id="temperatura" type="range" step="0.1" min="0" max="2" name="temperatura" value="{{ old('temperatura', $agente->temperatura ?? 0.7) }}" class="mt-2 block w-full" oninput="document.getElementById('temperatura_valor').textContent = this.value">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>0 — mais previsível</span>
                    <span id="temperatura_valor" class="font-medium text-gray-600">{{ old('temperatura', $agente->temperatura ?? 0.7) }}</span>
                    <span>2 — mais criativo</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">Esse é o mesmo parâmetro de "temperatura" usado na chamada à API do modelo.</p>
                <x-input-error :messages="$errors->get('temperatura')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="emojis" value="Uso de emojis *" />
                <select id="emojis" name="emojis" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach (['nunca' => 'Nunca usar', 'poucos' => 'Poucos', 'normal' => 'Normal', 'muito' => 'Muito'] as $valor => $label)
                        <option value="{{ $valor }}" @selected(old('emojis', $agente->emojis ?? 'normal') === $valor)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('emojis')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="tamanho_respostas" value="Tamanho das respostas *" />
                <select id="tamanho_respostas" name="tamanho_respostas" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach (['curtas' => 'Curtas', 'medias' => 'Médias', 'longas' => 'Longas'] as $valor => $label)
                        <option value="{{ $valor }}" @selected(old('tamanho_respostas', $agente->tamanho_respostas ?? 'medias') === $valor)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('tamanho_respostas')" class="mt-2" />
            </div>

            <div :class="tratamento === 'personalizado' ? '' : 'sm:col-span-2'">
                <x-input-label for="forma_tratamento" value="Forma de tratamento *" />
                <select id="forma_tratamento" name="forma_tratamento" x-model="tratamento" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach (['senhor' => 'Senhor(a)', 'voce' => 'Você', 'primeiro_nome' => 'Primeiro nome', 'personalizado' => 'Personalizado'] as $valor => $label)
                        <option value="{{ $valor }}" @selected(old('forma_tratamento', $agente->forma_tratamento ?? 'voce') === $valor)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('forma_tratamento')" class="mt-2" />
            </div>

            <div x-show="tratamento === 'personalizado'" x-cloak>
                <x-input-label for="forma_tratamento_personalizada" value="Como tratar o cliente" />
                <x-text-input id="forma_tratamento_personalizada" name="forma_tratamento_personalizada" class="mt-1 block w-full" value="{{ old('forma_tratamento_personalizada', $agente->forma_tratamento_personalizada ?? '') }}" placeholder="Ex: Doutor(a)" />
                <x-input-error :messages="$errors->get('forma_tratamento_personalizada')" class="mt-2" />
            </div>
        </div>
    </div>

    <div x-show="tab === 'prompt'" x-cloak>
        <div class="grid grid-cols-1 gap-6">
            <div>
                <x-input-label for="prompt_principal" value="Prompt principal *" />
                <textarea id="prompt_principal" name="prompt_principal" rows="6" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('prompt_principal', $agente->prompt_principal ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('prompt_principal')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="prompt_complementar" value="Prompt complementar" />
                <textarea id="prompt_complementar" name="prompt_complementar" rows="4" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('prompt_complementar', $agente->prompt_complementar ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('prompt_complementar')" class="mt-2" />
            </div>

            <div class="pt-2 border-t border-gray-100">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Prompts especializados</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <x-input-label for="prompt_horario_fechado" value="Prompt para horário fechado" />
                        <textarea id="prompt_horario_fechado" name="prompt_horario_fechado" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('prompt_horario_fechado', $agente->prompt_horario_fechado ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('prompt_horario_fechado')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="prompt_transferencia_humano" value="Prompt de transferência para humano" />
                        <textarea id="prompt_transferencia_humano" name="prompt_transferencia_humano" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('prompt_transferencia_humano', $agente->prompt_transferencia_humano ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('prompt_transferencia_humano')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="prompt_despedida" value="Prompt de despedida" />
                        <textarea id="prompt_despedida" name="prompt_despedida" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('prompt_despedida', $agente->prompt_despedida ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('prompt_despedida')" class="mt-2" />

                        <div class="flex items-center gap-2 mt-3">
                            <input type="hidden" name="enviar_link_avaliacao" value="0">
                            <input type="checkbox" id="enviar_link_avaliacao" name="enviar_link_avaliacao" value="1" @checked(old('enviar_link_avaliacao', $agente->enviar_link_avaliacao ?? false)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <x-input-label for="enviar_link_avaliacao" value="Enviar link de avaliação da conversa quando a IA identificar que o atendimento foi concluído" class="mb-0" />
                        </div>
                        <p class="text-xs text-gray-400 mt-1">A IA anexa o link automaticamente na resposta que encerra o atendimento — sem precisar copiar e enviar manualmente pelo painel.</p>
                        <x-input-error :messages="$errors->get('enviar_link_avaliacao')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="prompt_nao_sei_responder" value="Prompt quando não souber responder" />
                        <textarea id="prompt_nao_sei_responder" name="prompt_nao_sei_responder" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('prompt_nao_sei_responder', $agente->prompt_nao_sei_responder ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('prompt_nao_sei_responder')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="prompt_vendas" value="Prompt para vendas" />
                        <textarea id="prompt_vendas" name="prompt_vendas" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('prompt_vendas', $agente->prompt_vendas ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('prompt_vendas')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="prompt_suporte" value="Prompt para suporte" />
                        <textarea id="prompt_suporte" name="prompt_suporte" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('prompt_suporte', $agente->prompt_suporte ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('prompt_suporte')" class="mt-2" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $diasSemana = \App\Models\Agente::diasSemana();
        $diasDefault = [
            1 => ['fechado' => false, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            2 => ['fechado' => false, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            3 => ['fechado' => false, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            4 => ['fechado' => false, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            5 => ['fechado' => false, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            6 => ['fechado' => false, 'hora_inicio' => '08:00', 'hora_fim' => '12:00'],
            0 => ['fechado' => true, 'hora_inicio' => '', 'hora_fim' => ''],
        ];
        $horariosExistentes = ($agente->exists ?? false) ? $agente->horarios->keyBy('dia_semana') : collect();
        $horariosData = [];
        foreach (array_keys($diasSemana) as $dia) {
            $old = old("horarios.$dia");
            if ($old) {
                $horariosData[$dia] = [
                    'fechado' => ! empty($old['fechado']),
                    'hora_inicio' => $old['hora_inicio'] ?? '',
                    'hora_fim' => $old['hora_fim'] ?? '',
                ];
            } elseif ($horariosExistentes->has($dia)) {
                $h = $horariosExistentes->get($dia);
                $horariosData[$dia] = [
                    'fechado' => $h->fechado,
                    'hora_inicio' => $h->hora_inicio ? \Illuminate\Support\Carbon::parse($h->hora_inicio)->format('H:i') : '',
                    'hora_fim' => $h->hora_fim ? \Illuminate\Support\Carbon::parse($h->hora_fim)->format('H:i') : '',
                ];
            } else {
                $horariosData[$dia] = $diasDefault[$dia];
            }
        }

        $feriadosOld = old('feriados');
        if ($feriadosOld) {
            $feriadosData = collect($feriadosOld)->map(fn ($f) => ['data' => $f['data'] ?? '', 'descricao' => $f['descricao'] ?? ''])->values()->all();
        } elseif ($agente->exists ?? false) {
            $feriadosData = $agente->feriados->map(fn ($f) => ['data' => $f->data->format('Y-m-d'), 'descricao' => $f->descricao ?? ''])->values()->all();
        } else {
            $feriadosData = [];
        }
    @endphp
    <div x-show="tab === 'horarios'" x-cloak x-data="{ horarios: @js($horariosData), feriados: @js($feriadosData) }">
        @if (collect($errors->keys())->contains(fn ($k) => str_starts_with($k, 'horarios.') || str_starts_with($k, 'feriados.')))
            <p class="text-sm text-red-600 mb-4">Verifique os horários e feriados preenchidos — algum valor está inválido.</p>
        @endif

        <h3 class="text-sm font-medium text-gray-700 mb-3">Horário de funcionamento</h3>
        <div class="border border-gray-100 rounded-md divide-y divide-gray-100">
            @foreach ($diasSemana as $dia => $label)
                <div class="flex flex-wrap items-center gap-4 px-4 py-3">
                    <div class="w-24 text-sm font-medium text-gray-700 flex-shrink-0">{{ $label }}</div>

                    <label class="flex items-center gap-2 text-sm text-gray-500 flex-shrink-0">
                        <input type="checkbox" x-model="horarios[{{ $dia }}].fechado" name="horarios[{{ $dia }}][fechado]" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        Fechado
                    </label>

                    <template x-if="!horarios[{{ $dia }}].fechado">
                        <div class="flex items-center gap-2">
                            <input type="time" x-model="horarios[{{ $dia }}].hora_inicio" name="horarios[{{ $dia }}][hora_inicio]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <span class="text-gray-400 text-sm">até</span>
                            <input type="time" x-model="horarios[{{ $dia }}].hora_fim" name="horarios[{{ $dia }}][hora_fim]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <span class="text-xs text-gray-400">(em branco = 24h)</span>
                        </div>
                    </template>
                    <span x-show="horarios[{{ $dia }}].fechado" x-cloak class="text-sm text-gray-400">Fechado o dia todo</span>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-medium text-gray-700">Feriados</h3>
                <button type="button" @click="feriados.push({ data: '', descricao: '' })" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Adicionar feriado</button>
            </div>
            <p x-show="feriados.length === 0" class="text-sm text-gray-400">Nenhum feriado cadastrado.</p>
            <template x-for="(feriado, index) in feriados" :key="index">
                <div class="flex items-center gap-3 mb-2">
                    <input type="date" :name="`feriados[${index}][data]`" x-model="feriado.data" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    <input type="text" :name="`feriados[${index}][descricao]`" x-model="feriado.descricao" placeholder="Descrição (opcional)" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm flex-1">
                    <button type="button" @click="feriados.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">Remover</button>
                </div>
            </template>
        </div>

        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="sm:col-span-2">
                <x-input-label for="mensagem_fora_horario" value="Mensagem fora do horário" />
                <textarea id="mensagem_fora_horario" name="mensagem_fora_horario" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Enviada automaticamente quando o cliente escreve fora do horário de funcionamento">{{ old('mensagem_fora_horario', $agente->mensagem_fora_horario ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('mensagem_fora_horario')" class="mt-2" />
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <input type="hidden" name="transferencia_automatica_fora_horario" value="0">
                <input type="checkbox" id="transferencia_automatica_fora_horario" name="transferencia_automatica_fora_horario" value="1" @checked(old('transferencia_automatica_fora_horario', $agente->transferencia_automatica_fora_horario ?? false)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <x-input-label for="transferencia_automatica_fora_horario" value="Transferir automaticamente para humano fora do horário" class="mb-0" />
            </div>

            <div class="sm:col-span-2">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="retomar_ao_abrir_horario" value="0">
                    <input type="checkbox" id="retomar_ao_abrir_horario" name="retomar_ao_abrir_horario" value="1" @checked(old('retomar_ao_abrir_horario', $agente->retomar_ao_abrir_horario ?? false)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <x-input-label for="retomar_ao_abrir_horario" value="Retomar a conversa sozinha quando o horário de atendimento reabrir" class="mb-0" />
                </div>
                <p class="text-xs text-gray-400 mt-1 ml-6">Se o contato mandar mensagem fora do horário e não escrever de novo, a IA volta a responder sozinha assim que o horário reabrir — sem precisar esperar o contato voltar a puxar assunto. Só funciona dentro de 24h da última mensagem do contato (limite da própria Meta para mensagem livre).</p>
            </div>
        </div>
    </div>

    @php
        $faqsOld = old('faqs');
        if ($faqsOld) {
            $faqsData = collect($faqsOld)->map(fn ($f) => ['pergunta' => $f['pergunta'] ?? '', 'resposta' => $f['resposta'] ?? ''])->values()->all();
        } elseif ($agente->exists ?? false) {
            $faqsData = $agente->faqs->map(fn ($f) => ['pergunta' => $f->pergunta, 'resposta' => $f->resposta])->values()->all();
        } else {
            $faqsData = [];
        }

        $produtosOld = old('produtos');
        if ($produtosOld) {
            $existentesPorId = ($agente->exists ?? false) ? $agente->produtos->keyBy('id') : collect();
            $produtosData = collect($produtosOld)->map(function ($p) use ($existentesPorId) {
                $existente = ! empty($p['id']) ? $existentesPorId->get($p['id']) : null;

                return [
                    'id' => $p['id'] ?? null,
                    '_key' => 'row-' . ($p['id'] ?? uniqid()),
                    'tipo' => $p['tipo'] ?? 'produto',
                    'nome' => $p['nome'] ?? '',
                    'preco' => $p['preco'] ?? '',
                    'descricao' => $p['descricao'] ?? '',
                    'categoria' => $p['categoria'] ?? '',
                    'imagem_atual' => $existente?->imagem,
                ];
            })->values()->all();
        } elseif ($agente->exists ?? false) {
            $produtosData = $agente->produtos->map(fn ($p) => [
                'id' => $p->id,
                '_key' => 'row-' . $p->id,
                'tipo' => $p->tipo,
                'nome' => $p->nome,
                'preco' => $p->preco,
                'descricao' => $p->descricao,
                'categoria' => $p->categoria,
                'imagem_atual' => $p->imagem,
            ])->values()->all();
        } else {
            $produtosData = [];
        }

        $politicasOld = old('politicas');
        if ($politicasOld) {
            $politicasData = collect($politicasOld)->map(fn ($p) => ['titulo' => $p['titulo'] ?? '', 'conteudo' => $p['conteudo'] ?? ''])->values()->all();
        } elseif ($agente->exists ?? false) {
            $politicasData = $agente->politicas->map(fn ($p) => ['titulo' => $p->titulo, 'conteudo' => $p->conteudo])->values()->all();
        } else {
            $politicasData = [];
        }

        $documentosOld = old('documentos');
        if ($documentosOld) {
            $existentesDocPorId = ($agente->exists ?? false) ? $agente->documentos->keyBy('id') : collect();
            $documentosData = collect($documentosOld)->map(function ($d) use ($existentesDocPorId) {
                $existente = ! empty($d['id']) ? $existentesDocPorId->get($d['id']) : null;

                return [
                    'id' => $d['id'] ?? null,
                    '_key' => 'row-' . ($d['id'] ?? uniqid()),
                    'tipo' => $d['tipo'] ?? 'arquivo',
                    'nome' => $d['nome'] ?? '',
                    'url' => $d['url'] ?? '',
                    'descricao' => $d['descricao'] ?? '',
                    'arquivo_atual' => $existente?->arquivo,
                ];
            })->values()->all();
        } elseif ($agente->exists ?? false) {
            $documentosData = $agente->documentos->map(fn ($d) => [
                'id' => $d->id,
                '_key' => 'row-' . $d->id,
                'tipo' => $d->tipo,
                'nome' => $d->nome,
                'url' => $d->url,
                'descricao' => $d->descricao,
                'arquivo_atual' => $d->arquivo,
            ])->values()->all();
        } else {
            $documentosData = [];
        }
    @endphp
    <div x-show="tab === 'conhecimento'" x-cloak x-data="{ subtab: 'faq' }">
        <div class="border-b border-gray-100 mb-6">
            <nav class="flex gap-4 text-sm font-medium">
                <button type="button" @click="subtab = 'faq'" :class="subtab === 'faq' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">FAQ</button>
                <button type="button" @click="subtab = 'produtos'" :class="subtab === 'produtos' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Produtos/Serviços</button>
                <button type="button" @click="subtab = 'politicas'" :class="subtab === 'politicas' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Políticas</button>
                <button type="button" @click="subtab = 'documentos'" :class="subtab === 'documentos' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Documentos</button>
            </nav>
        </div>

        <div x-show="subtab === 'faq'" x-cloak x-data="{ faqs: @js($faqsData) }">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-400">Perguntas e respostas que o agente pode usar para responder direto, sem inventar.</p>
                <button type="button" @click="faqs.push({ pergunta: '', resposta: '' })" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex-shrink-0 ml-4">+ Adicionar pergunta</button>
            </div>
            <p x-show="faqs.length === 0" class="text-sm text-gray-400">Nenhuma pergunta cadastrada.</p>
            <template x-for="(faq, index) in faqs" :key="index">
                <div class="border border-gray-100 rounded-md p-4 mb-3">
                    <div class="flex items-start gap-3">
                        <div class="flex-1 space-y-2">
                            <input type="text" :name="`faqs[${index}][pergunta]`" x-model="faq.pergunta" placeholder="Pergunta" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <textarea :name="`faqs[${index}][resposta]`" x-model="faq.resposta" rows="2" placeholder="Resposta" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"></textarea>
                        </div>
                        <button type="button" @click="faqs.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm flex-shrink-0">Remover</button>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="subtab === 'produtos'" x-cloak x-data="{ produtos: @js($produtosData) }">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-400">Catálogo de produtos e serviços que o agente pode consultar ao responder.</p>
                <button type="button" @click="produtos.push({ id: null, _key: 'novo-' + Date.now() + '-' + Math.random(), tipo: 'produto', nome: '', preco: '', descricao: '', categoria: '', imagem_atual: null })" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex-shrink-0 ml-4">+ Adicionar item</button>
            </div>
            <p x-show="produtos.length === 0" class="text-sm text-gray-400">Nenhum produto/serviço cadastrado.</p>
            <template x-for="(produto, index) in produtos" :key="produto._key">
                <div class="border border-gray-100 rounded-md p-4 mb-3">
                    <input type="hidden" :name="`produtos[${index}][id]`" :value="produto.id">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 mb-3">
                        <div>
                            <label class="text-xs text-gray-500">Tipo</label>
                            <select :name="`produtos[${index}][tipo]`" x-model="produto.tipo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="produto">Produto</option>
                                <option value="servico">Serviço</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs text-gray-500">Nome</label>
                            <input type="text" :name="`produtos[${index}][nome]`" x-model="produto.nome" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Preço (R$)</label>
                            <input type="number" step="0.01" min="0" :name="`produtos[${index}][preco]`" x-model="produto.preco" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="text-xs text-gray-500">Categoria</label>
                            <input type="text" :name="`produtos[${index}][categoria]`" x-model="produto.categoria" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Imagem</label>
                            <div class="mt-1 flex items-center gap-2">
                                <template x-if="produto.imagem_atual">
                                    <img :src="'/storage/' + produto.imagem_atual" class="w-10 h-10 rounded object-cover border border-gray-200">
                                </template>
                                <input type="file" accept="image/*" :name="`produtos[${index}][imagem]`" class="block w-full text-xs text-gray-600 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                            </div>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="text-xs text-gray-500">Descrição</label>
                        <textarea :name="`produtos[${index}][descricao]`" x-model="produto.descricao" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"></textarea>
                    </div>
                    <button type="button" @click="produtos.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">Remover</button>
                </div>
            </template>
        </div>

        <div x-show="subtab === 'politicas'" x-cloak x-data="{ politicas: @js($politicasData) }">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-400">Ex: política de troca, entrega, cancelamento, garantia.</p>
                <button type="button" @click="politicas.push({ titulo: '', conteudo: '' })" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex-shrink-0 ml-4">+ Adicionar política</button>
            </div>
            <p x-show="politicas.length === 0" class="text-sm text-gray-400">Nenhuma política cadastrada.</p>
            <template x-for="(politica, index) in politicas" :key="index">
                <div class="border border-gray-100 rounded-md p-4 mb-3">
                    <div class="flex items-start gap-3">
                        <div class="flex-1 space-y-2">
                            <input type="text" :name="`politicas[${index}][titulo]`" x-model="politica.titulo" placeholder="Título (ex: Política de troca)" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                            <textarea :name="`politicas[${index}][conteudo]`" x-model="politica.conteudo" rows="3" placeholder="Conteúdo" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"></textarea>
                        </div>
                        <button type="button" @click="politicas.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm flex-shrink-0">Remover</button>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="subtab === 'documentos'" x-cloak x-data="{ documentos: @js($documentosData) }">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-400">Catálogos, PDFs e links de referência. No futuro, esse conteúdo poderá ser usado em busca semântica (RAG).</p>
                <button type="button" @click="documentos.push({ id: null, _key: 'novo-' + Date.now() + '-' + Math.random(), tipo: 'arquivo', nome: '', url: '', descricao: '', arquivo_atual: null })" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium flex-shrink-0 ml-4">+ Adicionar documento</button>
            </div>
            <p x-show="documentos.length === 0" class="text-sm text-gray-400">Nenhum documento cadastrado.</p>
            <template x-for="(documento, index) in documentos" :key="documento._key">
                <div class="border border-gray-100 rounded-md p-4 mb-3">
                    <input type="hidden" :name="`documentos[${index}][id]`" :value="documento.id">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                        <div>
                            <label class="text-xs text-gray-500">Tipo</label>
                            <select :name="`documentos[${index}][tipo]`" x-model="documento.tipo" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="arquivo">Arquivo (PDF/DOC)</option>
                                <option value="link">Link</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs text-gray-500">Nome</label>
                            <input type="text" :name="`documentos[${index}][nome]`" x-model="documento.nome" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                        </div>
                    </div>

                    <div class="mb-3" x-show="documento.tipo === 'arquivo'" x-cloak>
                        <label class="text-xs text-gray-500">Arquivo</label>
                        <div class="mt-1 flex items-center gap-2">
                            <template x-if="documento.arquivo_atual">
                                <a :href="'/storage/' + documento.arquivo_atual" target="_blank" class="text-xs text-indigo-600 underline flex-shrink-0">Arquivo atual</a>
                            </template>
                            <input type="file" accept=".pdf,.doc,.docx,.txt" :name="`documentos[${index}][arquivo]`" class="block w-full text-xs text-gray-600 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                    </div>

                    <div class="mb-3" x-show="documento.tipo === 'link'" x-cloak>
                        <label class="text-xs text-gray-500">URL</label>
                        <input type="url" :name="`documentos[${index}][url]`" x-model="documento.url" placeholder="https://..." class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                    </div>

                    <div class="mb-2">
                        <label class="text-xs text-gray-500">Descrição</label>
                        <textarea :name="`documentos[${index}][descricao]`" x-model="documento.descricao" rows="2" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"></textarea>
                    </div>

                    <button type="button" @click="documentos.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">Remover</button>
                </div>
            </template>
        </div>
    </div>

    @php
        $ferramentasSelecionadas = old('ferramentas_habilitadas', $agente->ferramentas_habilitadas ? explode(',', $agente->ferramentas_habilitadas) : []);
    @endphp
    <div x-show="tab === 'ferramentas'" x-cloak>
        <p class="text-sm text-gray-400 mb-4">Marque as ferramentas que este agente deve usar. Nenhuma delas funciona de verdade ainda — a integração com cada sistema (ERP, CRM, agenda...) será construída depois. Por enquanto isso só fica salvo como preferência.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach (\App\Models\Agente::ferramentasDisponiveis() as $valor => $label)
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="ferramentas_habilitadas[]" value="{{ $valor }}" @checked(in_array($valor, $ferramentasSelecionadas)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    {{ $label }}
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('ferramentas_habilitadas')" class="mt-2" />
    </div>

    @php
        $regrasOld = old('regras');
        if ($regrasOld) {
            $regrasData = array_values($regrasOld);
        } elseif ($agente->exists ?? false) {
            $regrasData = $agente->regras->pluck('regra')->values()->all();
        } else {
            $regrasData = [];
        }
    @endphp
    <div x-show="tab === 'regras'" x-cloak x-data="{ regras: @js($regrasData) }">
        <p class="text-sm text-gray-400 mb-4">Ex: "Nunca informar preços sem consultar catálogo", "Sempre confirmar telefone", "Nunca oferecer desconto".</p>

        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-medium text-gray-700">Regras</h3>
            <button type="button" @click="regras.push('')" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Adicionar regra</button>
        </div>
        <p x-show="regras.length === 0" class="text-sm text-gray-400">Nenhuma regra cadastrada.</p>
        <template x-for="(regra, index) in regras" :key="index">
            <div class="flex items-center gap-3 mb-2">
                <input type="text" :name="`regras[${index}]`" x-model="regras[index]" maxlength="500" class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                <button type="button" @click="regras.splice(index, 1)" class="text-red-500 hover:text-red-700 text-sm">Remover</button>
            </div>
        </template>
    </div>

    <div x-show="tab === 'memoria'" x-cloak x-data="{ habilitada: {{ old('memoria_habilitada', $agente->memoria_habilitada ?? true) ? 'true' : 'false' }} }">
        <div class="flex items-center gap-2 mb-6">
            <input type="hidden" name="memoria_habilitada" value="0">
            <input type="checkbox" id="memoria_habilitada" name="memoria_habilitada" value="1" x-model="habilitada" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            <x-input-label for="memoria_habilitada" value="Memória habilitada" class="mb-0" />
        </div>

        <div x-show="habilitada" x-cloak class="space-y-6">
            <div class="sm:w-64">
                <x-input-label for="memoria_dias_lembrar" value="Dias para lembrar *" />
                <x-text-input id="memoria_dias_lembrar" type="number" step="1" min="1" max="365" name="memoria_dias_lembrar" class="mt-1 block w-full" value="{{ old('memoria_dias_lembrar', $agente->memoria_dias_lembrar ?? 30) }}" />
                <x-input-error :messages="$errors->get('memoria_dias_lembrar')" class="mt-2" />
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="memoria_resumo_automatico" value="0">
                <input type="checkbox" id="memoria_resumo_automatico" name="memoria_resumo_automatico" value="1" @checked(old('memoria_resumo_automatico', $agente->memoria_resumo_automatico ?? true)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <x-input-label for="memoria_resumo_automatico" value="Resumo automático da conversa" class="mb-0" />
            </div>

            <div>
                <p class="text-sm font-medium text-gray-700 mb-2">O que salvar da conversa</p>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="memoria_salvar_preferencias" value="0">
                        <input type="checkbox" id="memoria_salvar_preferencias" name="memoria_salvar_preferencias" value="1" @checked(old('memoria_salvar_preferencias', $agente->memoria_salvar_preferencias ?? true)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <x-input-label for="memoria_salvar_preferencias" value="Preferências" class="mb-0" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="memoria_salvar_nome" value="0">
                        <input type="checkbox" id="memoria_salvar_nome" name="memoria_salvar_nome" value="1" @checked(old('memoria_salvar_nome', $agente->memoria_salvar_nome ?? true)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <x-input-label for="memoria_salvar_nome" value="Nome" class="mb-0" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="memoria_salvar_endereco" value="0">
                        <input type="checkbox" id="memoria_salvar_endereco" name="memoria_salvar_endereco" value="1" @checked(old('memoria_salvar_endereco', $agente->memoria_salvar_endereco ?? false)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <x-input-label for="memoria_salvar_endereco" value="Endereço" class="mb-0" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="tab === 'modelo'" x-cloak>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-input-label for="modelo" value="Modelo *" />
                <x-text-input id="modelo" name="modelo" class="mt-1 block w-full" value="{{ old('modelo', $agente->modelo ?? 'gpt-4o-mini') }}" />
                <p class="text-xs text-gray-400 mt-1">Identificador do modelo na API da OpenAI (ex: gpt-4o-mini).</p>
                <x-input-error :messages="$errors->get('modelo')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="modelo_fallback" value="Modelo de fallback" />
                <x-text-input id="modelo_fallback" name="modelo_fallback" class="mt-1 block w-full" value="{{ old('modelo_fallback', $agente->modelo_fallback ?? '') }}" placeholder="Ex: gpt-4o-mini" />
                <p class="text-xs text-gray-400 mt-1">Usado se o modelo principal ficar indisponível.</p>
                <x-input-error :messages="$errors->get('modelo_fallback')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="top_p" value="Top P *" />
                <x-text-input id="top_p" type="number" step="0.01" min="0" max="1" name="top_p" class="mt-1 block w-full" value="{{ old('top_p', $agente->top_p ?? 1) }}" />
                <p class="text-xs text-gray-400 mt-1">0 a 1 — alternativa à temperatura para controlar aleatoriedade. Deixe 1 se não souber o que ajustar.</p>
                <x-input-error :messages="$errors->get('top_p')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="max_tokens" value="Máximo de tokens por resposta" />
                <x-text-input id="max_tokens" type="number" step="1" min="1" max="128000" name="max_tokens" class="mt-1 block w-full" value="{{ old('max_tokens', $agente->max_tokens ?? '') }}" placeholder="Deixe em branco para usar o padrão do modelo" />
                <x-input-error :messages="$errors->get('max_tokens')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="frequency_penalty" value="Frequency Penalty *" />
                <x-text-input id="frequency_penalty" type="number" step="0.01" min="-2" max="2" name="frequency_penalty" class="mt-1 block w-full" value="{{ old('frequency_penalty', $agente->frequency_penalty ?? 0) }}" />
                <p class="text-xs text-gray-400 mt-1">-2 a 2 — valores maiores reduzem repetição de palavras.</p>
                <x-input-error :messages="$errors->get('frequency_penalty')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="presence_penalty" value="Presence Penalty *" />
                <x-text-input id="presence_penalty" type="number" step="0.01" min="-2" max="2" name="presence_penalty" class="mt-1 block w-full" value="{{ old('presence_penalty', $agente->presence_penalty ?? 0) }}" />
                <p class="text-xs text-gray-400 mt-1">-2 a 2 — valores maiores incentivam falar sobre assuntos novos.</p>
                <x-input-error :messages="$errors->get('presence_penalty')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="timeout" value="Timeout (segundos) *" />
                <x-text-input id="timeout" type="number" step="1" min="1" max="300" name="timeout" class="mt-1 block w-full" value="{{ old('timeout', $agente->timeout ?? 30) }}" />
                <p class="text-xs text-gray-400 mt-1">Tempo máximo de espera pela resposta da API antes de desistir.</p>
                <x-input-error :messages="$errors->get('timeout')" class="mt-2" />
            </div>
        </div>
    </div>

    <div x-show="tab === 'limites'" x-cloak>
        <p class="text-sm text-gray-400 mb-6">Deixe em branco para não aplicar limite.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <x-input-label for="limite_mensagens_conversa" value="Máximo de mensagens por conversa" />
                <x-text-input id="limite_mensagens_conversa" type="number" step="1" min="1" name="limite_mensagens_conversa" class="mt-1 block w-full" value="{{ old('limite_mensagens_conversa', $agente->limite_mensagens_conversa ?? '') }}" />
                <x-input-error :messages="$errors->get('limite_mensagens_conversa')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="limite_tokens_conversa" value="Máximo de tokens por conversa" />
                <x-text-input id="limite_tokens_conversa" type="number" step="1" min="1" name="limite_tokens_conversa" class="mt-1 block w-full" value="{{ old('limite_tokens_conversa', $agente->limite_tokens_conversa ?? '') }}" />
                <x-input-error :messages="$errors->get('limite_tokens_conversa')" class="mt-2" />
            </div>

            <div class="sm:col-span-2">
                <x-input-label for="prompt_limite_atingido" value="Orientação de aviso ao atingir qualquer limite" />
                <textarea id="prompt_limite_atingido" name="prompt_limite_atingido" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Ex: Avise o contato de forma simpática que a conversa chegou até aqui por enquanto, e que a equipe vai continuar o atendimento em breve.">{{ old('prompt_limite_atingido', $agente->prompt_limite_atingido ?? '') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">Não é um texto pronto pra copiar — é uma instrução de como a IA deve se comportar. Ela usa isso pra montar a última resposta com as próprias palavras (no estilo dela) quando qualquer um dos limites desta aba é atingido — mensagens/tokens da conversa, ou mensagens por minuto/por dia do agente — já sendo transferida pra um humano nesse momento. Deixe em branco pra IA simplesmente parar de responder, sem avisar o contato.</p>
                <x-input-error :messages="$errors->get('prompt_limite_atingido')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="limite_mensagens_minuto" value="Máximo de mensagens por minuto" />
                <x-text-input id="limite_mensagens_minuto" type="number" step="1" min="1" name="limite_mensagens_minuto" class="mt-1 block w-full" value="{{ old('limite_mensagens_minuto', $agente->limite_mensagens_minuto ?? '') }}" />
                <p class="text-xs text-gray-400 mt-1">Proteção contra flood/abuso por um mesmo contato. Ao atingir, também usa a orientação de aviso acima (se configurada).</p>
                <x-input-error :messages="$errors->get('limite_mensagens_minuto')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="limite_mensagens_dia" value="Máximo de mensagens por dia" />
                <x-text-input id="limite_mensagens_dia" type="number" step="1" min="1" name="limite_mensagens_dia" class="mt-1 block w-full" value="{{ old('limite_mensagens_dia', $agente->limite_mensagens_dia ?? '') }}" />
                <p class="text-xs text-gray-400 mt-1">Controle de custo — limite diário do agente (soma de todas as conversas). Ao atingir, também usa a orientação de aviso acima (se configurada).</p>
                <x-input-error :messages="$errors->get('limite_mensagens_dia')" class="mt-2" />
            </div>

            <div class="sm:col-span-2 border-t border-gray-200 pt-4"
                 x-data="{ retomadaHabilitada: {{ (old('retomada_automatica_minutos', $agente->retomada_automatica_minutos ?? null)) ? 'true' : 'false' }} }">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" x-model="retomadaHabilitada" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="text-sm font-medium text-gray-700">Retomar sozinha após atendimento humano</span>
                </label>
                <p class="text-xs text-gray-400 mt-1">Quando um humano assume a conversa (pelo celular ou pelo painel) e o contato responde depois de um tempo sem o humano ter mandado nada de novo, a IA volta a responder sozinha, sem precisar de "Devolver atendimento para a IA" manual.</p>

                <div x-show="retomadaHabilitada" x-cloak class="mt-4 space-y-4">
                    <div>
                        <x-input-label for="retomada_automatica_minutos" value="Depois de quantos minutos sem o humano responder" />
                        <x-text-input id="retomada_automatica_minutos" type="number" step="1" min="1" name="retomada_automatica_minutos" class="mt-1 block w-full sm:w-48" value="{{ old('retomada_automatica_minutos', $agente->retomada_automatica_minutos ?? '') }}" placeholder="Ex: 60" x-bind:disabled="!retomadaHabilitada" />
                        <x-input-error :messages="$errors->get('retomada_automatica_minutos')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="prompt_retomada_automatica" value="Orientação de comportamento ao retomar sozinha (opcional)" />
                        <textarea id="prompt_retomada_automatica" name="prompt_retomada_automatica" rows="3" x-bind:disabled="!retomadaHabilitada" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Ex: Reconheça com naturalidade que demorou a responder, sem repetir a saudação inicial, e confirme se o assunto de antes ainda é o que a pessoa precisa.">{{ old('prompt_retomada_automatica', $agente->prompt_retomada_automatica ?? '') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Como a IA deve se comportar especificamente nesse momento de retomar sozinha — não é texto pronto, é uma instrução (mesmo formato do aviso de limite acima). Deixe em branco para ela responder normalmente, sem nenhum cuidado especial de retomada.</p>
                        <x-input-error :messages="$errors->get('prompt_retomada_automatica')" class="mt-2" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $tiposSelecionados = old('tipos_anexos_permitidos', $agente->tipos_anexos_permitidos ? explode(',', $agente->tipos_anexos_permitidos) : []);
    @endphp
    <div x-show="tab === 'seguranca'" x-cloak x-data="{ anexos: {{ old('permitir_anexos', $agente->permitir_anexos ?? true) ? 'true' : 'false' }} }">
        <div class="space-y-4">
            <div class="flex items-center gap-2">
                <input type="hidden" name="permitir_atendimento_humano" value="0">
                <input type="checkbox" id="permitir_atendimento_humano" name="permitir_atendimento_humano" value="1" @checked(old('permitir_atendimento_humano', $agente->permitir_atendimento_humano ?? true)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <x-input-label for="permitir_atendimento_humano" value="Permitir atendimento humano" class="mb-0" />
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="mascarar_cpf" value="0">
                <input type="checkbox" id="mascarar_cpf" name="mascarar_cpf" value="1" @checked(old('mascarar_cpf', $agente->mascarar_cpf ?? true)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <x-input-label for="mascarar_cpf" value="Mascarar CPF nas mensagens registradas" class="mb-0" />
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="mascarar_cartao" value="0">
                <input type="checkbox" id="mascarar_cartao" name="mascarar_cartao" value="1" @checked(old('mascarar_cartao', $agente->mascarar_cartao ?? true)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <x-input-label for="mascarar_cartao" value="Mascarar número de cartão de crédito" class="mb-0" />
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="permitir_anexos" value="0">
                <input type="checkbox" id="permitir_anexos" name="permitir_anexos" value="1" x-model="anexos" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <x-input-label for="permitir_anexos" value="Permitir anexos" class="mb-0" />
            </div>

            <div x-show="anexos" x-cloak class="pl-6">
                <x-input-label value="Tipos de arquivo permitidos" />
                <div class="mt-2 flex flex-wrap gap-4">
                    @foreach (['imagem' => 'Imagem', 'documento' => 'Documento (PDF/DOC)', 'audio' => 'Áudio', 'video' => 'Vídeo'] as $valor => $label)
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="tipos_anexos_permitidos[]" value="{{ $valor }}" @checked(in_array($valor, $tiposSelecionados)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('tipos_anexos_permitidos')" class="mt-2" />
            </div>
        </div>
    </div>
</div>

<div class="flex items-center gap-4 mt-6 pt-6 border-t border-gray-100">
    <x-primary-button>{{ __('Salvar') }}</x-primary-button>
    <a href="{{ route('clientes.agentes.index', $cliente) }}" class="text-sm text-gray-600 underline">Cancelar</a>
</div>
