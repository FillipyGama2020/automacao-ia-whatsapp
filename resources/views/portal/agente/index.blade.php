<x-portal-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Meu Agente') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="bg-red-100 border border-red-300 text-red-800 text-sm rounded-md p-4">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $erro)
                            <li>{{ $erro }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $agenteAbertoId = (int) request('agente');
                $tabAberta = request('tab', 'geral');
                $diasSemana = \App\Models\Agente::diasSemana();
            @endphp

            @forelse ($agentes as $agente)
                @php
                    $horariosPorDia = $agente->horarios->keyBy('dia_semana');
                    $horariosData = collect(array_keys($diasSemana))->map(fn ($dia) => [
                        'fechado' => $horariosPorDia->get($dia)?->fechado ?? false,
                        'hora_inicio' => $horariosPorDia->get($dia)?->hora_inicio ? substr($horariosPorDia->get($dia)->hora_inicio, 0, 5) : '',
                        'hora_fim' => $horariosPorDia->get($dia)?->hora_fim ? substr($horariosPorDia->get($dia)->hora_fim, 0, 5) : '',
                    ])->all();
                    $feriadosData = $agente->feriados->map(fn ($f) => ['data' => $f->data->format('Y-m-d'), 'descricao' => $f->descricao])->values()->all();
                    $regrasData = $agente->regras->pluck('regra')->values()->all();
                    $faqsData = $agente->faqs->map(fn ($f) => ['pergunta' => $f->pergunta, 'resposta' => $f->resposta])->values()->all();
                    $produtosData = $agente->produtos->map(fn ($p) => [
                        'id' => $p->id, '_key' => 'row-' . $p->id, 'tipo' => $p->tipo, 'nome' => $p->nome,
                        'preco' => $p->preco, 'descricao' => $p->descricao, 'categoria' => $p->categoria, 'imagem_atual' => $p->imagem,
                    ])->values()->all();
                    $politicasData = $agente->politicas->map(fn ($p) => ['titulo' => $p->titulo, 'conteudo' => $p->conteudo])->values()->all();
                    $documentosData = $agente->documentos->map(fn ($d) => [
                        'id' => $d->id, '_key' => 'row-' . $d->id, 'tipo' => $d->tipo, 'nome' => $d->nome,
                        'url' => $d->url, 'descricao' => $d->descricao, 'arquivo_atual' => $d->arquivo,
                    ])->values()->all();
                @endphp
                <div x-data="{ aberto: {{ $agente->id === $agenteAbertoId ? 'true' : 'false' }}, tab: '{{ $agente->id === $agenteAbertoId ? $tabAberta : 'geral' }}' }" class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <button type="button" @click="aberto = !aberto" class="w-full flex items-center gap-3 p-4 text-left hover:bg-gray-50 focus:outline-none">
                        @if ($agente->avatar)
                            <img src="{{ asset('storage/'.$agente->avatar) }}" class="w-12 h-12 rounded-full object-cover border border-gray-200 flex-shrink-0" alt="">
                        @else
                            <span class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: {{ $agente->cor ?? '#6366f1' }}">
                                <x-icon name="headset" class="w-6 h-6 text-white" />
                            </span>
                        @endif

                        <span class="text-lg font-semibold text-gray-800">{{ $agente->nome }}</span>

                        <span @class([
                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                            'bg-green-100 text-green-800' => $agente->ativo,
                            'bg-gray-100 text-gray-800' => ! $agente->ativo,
                        ])>
                            {{ $agente->ativo ? 'Ativo' : 'Inativo' }}
                        </span>

                        <x-icon name="chevron-down" class="w-5 h-5 ml-auto flex-shrink-0 text-gray-400 transition-transform duration-200" x-bind:class="{ 'rotate-180': aberto }" />
                    </button>

                    <div x-show="aberto" x-cloak x-transition class="border-t border-gray-100">
                        <div class="border-b border-gray-100 px-6 overflow-x-auto">
                            <nav class="flex gap-4 min-w-max text-sm font-medium">
                                <button type="button" @click="tab = 'geral'" :class="tab === 'geral' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 pt-4 px-1">Informações Gerais</button>
                                <button type="button" @click="tab = 'horarios'" :class="tab === 'horarios' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 pt-4 px-1">Horário de Funcionamento</button>
                                <button type="button" @click="tab = 'regras'" :class="tab === 'regras' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 pt-4 px-1">Regras de Negócio</button>
                                <button type="button" @click="tab = 'conhecimento'" :class="tab === 'conhecimento' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 pt-4 px-1">Conhecimento</button>
                                <a href="{{ route('portal.contatos-bloqueados.index') }}" class="border-b-2 border-transparent text-gray-500 hover:text-gray-700 pb-3 pt-4 px-1 flex items-center gap-1">
                                    Bloqueios
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-3 h-3"><path d="M7 17L17 7M7 7h10v10"/></svg>
                                </a>
                            </nav>
                        </div>

                        <div class="p-6">

                            <form x-show="tab === 'geral'" x-cloak method="POST" action="{{ route('portal.agente.update-geral', $agente) }}" enctype="multipart/form-data" class="space-y-6">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="sm:col-span-2 flex items-center gap-4">
                                        @if ($agente->avatar)
                                            <img src="{{ asset('storage/' . $agente->avatar) }}" class="w-16 h-16 rounded-full object-cover bg-gray-100 border border-gray-200" alt="">
                                        @else
                                            <div class="w-16 h-16 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center">
                                                <x-icon name="headset" class="w-6 h-6 text-gray-300" />
                                            </div>
                                        @endif
                                        <div class="flex-1">
                                            <label class="block text-sm font-medium text-gray-700">Foto (avatar)</label>
                                            <input type="file" name="avatar" accept="image/*" class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                            <p class="text-xs text-gray-400 mt-1">JPG, PNG ou GIF, até 8MB.</p>
                                        </div>
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Nome do agente *</label>
                                        <input type="text" name="nome" value="{{ $agente->nome }}" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    </div>

                                    <div class="sm:col-span-2">
                                        <label class="block text-sm font-medium text-gray-700">Objetivo</label>
                                        <input type="text" name="objetivo" value="{{ $agente->objetivo }}" placeholder="Ex: Atender dúvidas sobre produtos e qualificar leads" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Departamento</label>
                                        <input type="text" name="departamento" value="{{ $agente->departamento }}" placeholder="Ex: Vendas, Suporte, Financeiro" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Cor do agente</label>
                                        <input type="color" name="cor" value="{{ $agente->cor ?? '#6366f1' }}" class="mt-1 h-9 w-14 rounded border border-gray-300 cursor-pointer">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Idioma *</label>
                                        <select name="idioma" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                            @foreach (['pt-BR' => 'Português (Brasil)', 'en-US' => 'Inglês (EUA)', 'es-ES' => 'Espanhol'] as $valor => $label)
                                                <option value="{{ $valor }}" @selected($agente->idioma === $valor)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Timezone *</label>
                                        <select name="timezone" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                            @foreach ([
                                                'America/Sao_Paulo' => 'Brasília (UTC-3)',
                                                'America/Manaus' => 'Manaus (UTC-4)',
                                                'America/Belem' => 'Belém (UTC-3)',
                                                'America/Rio_Branco' => 'Rio Branco (UTC-5)',
                                                'America/Noronha' => 'Fernando de Noronha (UTC-2)',
                                            ] as $valor => $label)
                                                <option value="{{ $valor }}" @selected($agente->timezone === $valor)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-gray-100" x-data="{ tratamento: '{{ $agente->forma_tratamento ?? 'voce' }}' }">
                                    <h3 class="text-sm font-medium text-gray-700 mb-4">Personalidade</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                        <div class="sm:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700">Nome que a IA utilizará</label>
                                            <input type="text" name="nome_ia" value="{{ $agente->nome_ia }}" placeholder='Ex: "Sou a Ana, assistente virtual da Clínica XYZ"' class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Papel</label>
                                            <select name="papel" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                <option value="">Selecione...</option>
                                                @foreach (['Secretária', 'Vendedora', 'Suporte', 'Recepcionista', 'Pós-venda', 'Financeiro', 'RH'] as $opcao)
                                                    <option value="{{ $opcao }}" @selected($agente->papel === $opcao)>{{ $opcao }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Tom de voz</label>
                                            <select name="tom_voz" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                <option value="">Selecione...</option>
                                                @foreach (['Formal', 'Amigável', 'Profissional', 'Jovem', 'Divertido', 'Premium', 'Técnico'] as $opcao)
                                                    <option value="{{ $opcao }}" @selected($agente->tom_voz === $opcao)>{{ $opcao }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Uso de emojis *</label>
                                            <select name="emojis" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                @foreach (['nunca' => 'Nunca usar', 'poucos' => 'Poucos', 'normal' => 'Normal', 'muito' => 'Muito'] as $valor => $label)
                                                    <option value="{{ $valor }}" @selected(($agente->emojis ?? 'normal') === $valor)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Tamanho das respostas *</label>
                                            <select name="tamanho_respostas" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                @foreach (['curtas' => 'Curtas', 'medias' => 'Médias', 'longas' => 'Longas'] as $valor => $label)
                                                    <option value="{{ $valor }}" @selected(($agente->tamanho_respostas ?? 'medias') === $valor)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div :class="tratamento === 'personalizado' ? '' : 'sm:col-span-2'">
                                            <label class="block text-sm font-medium text-gray-700">Forma de tratamento *</label>
                                            <select name="forma_tratamento" x-model="tratamento" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                @foreach (['senhor' => 'Senhor(a)', 'voce' => 'Você', 'primeiro_nome' => 'Primeiro nome', 'personalizado' => 'Personalizado'] as $valor => $label)
                                                    <option value="{{ $valor }}" @selected(($agente->forma_tratamento ?? 'voce') === $valor)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div x-show="tratamento === 'personalizado'" x-cloak>
                                            <label class="block text-sm font-medium text-gray-700">Como tratar o cliente</label>
                                            <input type="text" name="forma_tratamento_personalizada" value="{{ $agente->forma_tratamento_personalizada }}" placeholder="Ex: Doutor(a)" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-2">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Salvar
                                    </button>
                                </div>
                            </form>

                            <form x-show="tab === 'horarios'" x-cloak method="POST" action="{{ route('portal.agente.update-horarios', $agente) }}" x-data="{ horarios: @js($horariosData), feriados: @js($feriadosData) }">
                                @csrf
                                @method('PUT')

                                <div class="border border-gray-100 rounded-md divide-y divide-gray-100">
                                    @foreach ($diasSemana as $dia => $label)
                                        <div class="flex items-center gap-4 px-4 py-3 text-sm">
                                            <span class="w-28 flex-shrink-0 text-gray-600">{{ $label }}</span>
                                            <label class="flex items-center gap-2 text-xs text-gray-500">
                                                <input type="checkbox" x-model="horarios[{{ $dia }}].fechado" name="horarios[{{ $dia }}][fechado]" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                Fechado
                                            </label>
                                            <template x-if="!horarios[{{ $dia }}].fechado">
                                                <div class="flex items-center gap-2">
                                                    <input type="time" x-model="horarios[{{ $dia }}].hora_inicio" name="horarios[{{ $dia }}][hora_inicio]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                    <span class="text-gray-400">até</span>
                                                    <input type="time" x-model="horarios[{{ $dia }}].hora_fim" name="horarios[{{ $dia }}][hora_fim]" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                    <span class="text-xs text-gray-400">(em branco = 24h)</span>
                                                </div>
                                            </template>
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

                                <div class="mt-8 grid grid-cols-1 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Mensagem fora do horário</label>
                                        <textarea name="mensagem_fora_horario" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" placeholder="Enviada automaticamente quando o cliente escreve fora do horário de funcionamento">{{ $agente->mensagem_fora_horario }}</textarea>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <input type="hidden" name="transferencia_automatica_fora_horario" value="0">
                                        <input type="checkbox" id="tafh-{{ $agente->id }}" name="transferencia_automatica_fora_horario" value="1" @checked($agente->transferencia_automatica_fora_horario) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <label for="tafh-{{ $agente->id }}" class="text-sm text-gray-700">Transferir automaticamente para humano fora do horário</label>
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <input type="hidden" name="retomar_ao_abrir_horario" value="0">
                                            <input type="checkbox" id="raah-{{ $agente->id }}" name="retomar_ao_abrir_horario" value="1" @checked($agente->retomar_ao_abrir_horario) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <label for="raah-{{ $agente->id }}" class="text-sm text-gray-700">Retomar a conversa sozinha quando o horário de atendimento reabrir</label>
                                        </div>
                                        <p class="text-xs text-gray-400 mt-1 ml-6">Se o contato mandar mensagem fora do horário e não escrever de novo, a IA volta a responder sozinha assim que o horário reabrir.</p>
                                    </div>
                                </div>

                                <div class="pt-6">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Salvar
                                    </button>
                                </div>
                            </form>

                            <form x-show="tab === 'regras'" x-cloak method="POST" action="{{ route('portal.agente.update-regras', $agente) }}" x-data="{ regras: @js($regrasData) }">
                                @csrf
                                @method('PUT')

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

                                <div class="pt-6">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Salvar
                                    </button>
                                </div>
                            </form>

                            <form x-show="tab === 'conhecimento'" x-cloak method="POST" action="{{ route('portal.agente.update-conhecimento', $agente) }}" enctype="multipart/form-data" x-data="{ subtab: 'faq', faqs: @js($faqsData), produtos: @js($produtosData), politicas: @js($politicasData), documentos: @js($documentosData) }">
                                @csrf
                                @method('PUT')

                                <div class="border-b border-gray-100 mb-6">
                                    <nav class="flex gap-4 text-sm font-medium">
                                        <button type="button" @click="subtab = 'faq'" :class="subtab === 'faq' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">FAQ</button>
                                        <button type="button" @click="subtab = 'produtos'" :class="subtab === 'produtos' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Produtos/Serviços</button>
                                        <button type="button" @click="subtab = 'politicas'" :class="subtab === 'politicas' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Políticas</button>
                                        <button type="button" @click="subtab = 'documentos'" :class="subtab === 'documentos' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700'" class="border-b-2 pb-3 px-1">Documentos</button>
                                    </nav>
                                </div>

                                <div x-show="subtab === 'faq'" x-cloak>
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

                                <div x-show="subtab === 'produtos'" x-cloak>
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

                                <div x-show="subtab === 'politicas'" x-cloak>
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

                                <div x-show="subtab === 'documentos'" x-cloak>
                                    <div class="flex items-center justify-between mb-3">
                                        <p class="text-sm text-gray-400">Catálogos, PDFs e links de referência.</p>
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

                                <div class="pt-6">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                        Salvar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-sm text-gray-500">
                    Nenhum agente configurado ainda.
                </div>
            @endforelse
        </div>
    </div>
</x-portal-layout>
