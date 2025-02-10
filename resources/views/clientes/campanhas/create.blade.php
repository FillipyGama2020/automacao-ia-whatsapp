<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.campanhas.index', $cliente) }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Nova campanha — ') }}{{ $cliente->nome_empresa }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="bg-red-100 border border-red-300 text-red-800 text-sm rounded-md p-4 mb-4">
                    {{ session('error') }}
                </div>
            @endif

            @if ($templates->isEmpty())
                <div class="bg-amber-50 border border-amber-300 rounded-md p-4 text-sm text-amber-800">
                    Este cliente não tem nenhum template aprovado ainda — crie e submeta um template antes de montar uma campanha.
                </div>
            @else
                <div
                    x-data="{
                        templates: @js($templatesData),
                        camposLead: @js(\App\Models\Lead::camposMapeaveisCampanha()),
                        templateId: '{{ old('message_template_id', '') }}',
                        tipoDestinatario: '{{ old('tipo_destinatario', 'lote') }}',
                        variaveisState: @js(old('variaveis', [])),
                        get template() { return this.templateId ? this.templates[this.templateId] : null; },
                        get posicoes() { return this.template ? this.template.posicoes : []; },
                        onTemplateChange() {
                            const novo = {};
                            this.posicoes.forEach((p) => {
                                novo[p] = (this.variaveisState[p]) || { tipo: 'campo', valor: '' };
                            });
                            this.variaveisState = novo;
                        },
                    }"
                    x-init="onTemplateChange()"
                    class="bg-white shadow-sm sm:rounded-lg p-6"
                >
                    <form method="POST" action="{{ route('clientes.campanhas.store', $cliente) }}">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <x-input-label for="message_template_id" value="Template aprovado *" />
                                <select id="message_template_id" name="message_template_id" x-model="templateId" @change="onTemplateChange()" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                    <option value="">Selecione...</option>
                                    @foreach ($templates as $template)
                                        <option value="{{ $template->id }}" @selected(old('message_template_id') == $template->id)>{{ $template->nome }} ({{ \App\Models\MessageTemplate::categoriaLabels()[$template->categoria] }})</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('message_template_id')" class="mt-2" />
                                <p x-show="template" x-cloak class="mt-2 text-xs text-gray-500 bg-gray-50 rounded-md p-3" x-text="template ? template.corpo : ''"></p>
                            </div>

                            <div>
                                <x-input-label value="Destinatários *" />
                                <div class="flex gap-6 mt-1">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="radio" name="tipo_destinatario" value="lote" x-model="tipoDestinatario" class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700">Em lote (filtro)</span>
                                    </label>
                                    <label class="inline-flex items-center gap-2">
                                        <input type="radio" name="tipo_destinatario" value="individual" x-model="tipoDestinatario" class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700">Individual</span>
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('tipo_destinatario')" class="mt-2" />
                            </div>

                            <div x-show="tipoDestinatario === 'lote'" x-cloak>
                                <x-input-label for="filtro_lote" value="Filtro *" />
                                <select id="filtro_lote" name="filtro_lote" class="mt-1 block w-full sm:w-1/2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    @foreach (\App\Models\Campanha::filtroLoteLabels() as $valor => $label)
                                        <option value="{{ $valor }}" @selected(old('filtro_lote', 'todos') === $valor)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Leads em opt-out nunca entram, mesmo dentro do filtro escolhido.</p>
                                <x-input-error :messages="$errors->get('filtro_lote')" class="mt-2" />
                            </div>

                            <div x-show="tipoDestinatario === 'individual'" x-cloak>
                                <x-input-label for="lead_id" value="Lead *" />
                                <select id="lead_id" name="lead_id" class="mt-1 block w-full sm:w-1/2 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($leads as $lead)
                                        <option value="{{ $lead->id }}" @selected(old('lead_id') == $lead->id)>{{ $lead->nome ?? 'Sem nome' }} — {{ $lead->telefone }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('lead_id')" class="mt-2" />
                            </div>

                            <div x-show="posicoes.length > 0" x-cloak>
                                <x-input-label value="Variáveis do template" />
                                <p class="text-xs text-gray-500 mb-3">Pra cada variável, escolha um campo do lead (preenche automático por destinatário) ou digite um valor fixo (mesmo texto pra todos).</p>

                                <template x-for="posicao in posicoes" :key="posicao">
                                    <div class="border border-gray-200 rounded-md p-3 mb-3">
                                        <div class="text-xs font-mono text-gray-500 mb-2" x-text="'{{' + posicao + '}}'"></div>
                                        <div class="flex flex-wrap items-center gap-3">
                                            <label class="inline-flex items-center gap-1.5">
                                                <input type="radio" :name="`variaveis_tipo_${posicao}`" value="campo" x-model="variaveisState[posicao].tipo" class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-sm text-gray-700">Campo do lead</span>
                                            </label>
                                            <label class="inline-flex items-center gap-1.5">
                                                <input type="radio" :name="`variaveis_tipo_${posicao}`" value="fixo" x-model="variaveisState[posicao].tipo" class="border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-sm text-gray-700">Valor fixo</span>
                                            </label>
                                            <input type="hidden" :name="`variaveis[${posicao}][tipo]`" :value="variaveisState[posicao].tipo">

                                            <template x-if="variaveisState[posicao].tipo === 'campo'">
                                                <select :name="`variaveis[${posicao}][valor]`" x-model="variaveisState[posicao].valor" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                                    <option value="">Selecione...</option>
                                                    <template x-for="(label, campo) in camposLead" :key="campo">
                                                        <option :value="campo" x-text="label"></option>
                                                    </template>
                                                </select>
                                            </template>
                                            <template x-if="variaveisState[posicao].tipo === 'fixo'">
                                                <input type="text" :name="`variaveis[${posicao}][valor]`" x-model="variaveisState[posicao].valor" placeholder="Valor pra todos os destinatários" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm flex-1 min-w-[12rem]">
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div>
                                <x-input-label for="agendado_para" value="Agendar para (opcional)" />
                                <x-text-input id="agendado_para" type="datetime-local" name="agendado_para" class="mt-1 block w-full sm:w-1/2" value="{{ old('agendado_para') }}" />
                                <p class="mt-1 text-xs text-gray-500">Deixe em branco pra decidir o momento do envio depois, na tela da campanha ("Enviar agora"). Se preencher, a campanha dispara sozinha nesse horário.</p>
                                <x-input-error :messages="$errors->get('agendado_para')" class="mt-2" />
                            </div>
                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button>{{ __('Criar campanha') }}</x-primary-button>
                            <a href="{{ route('clientes.campanhas.index', $cliente) }}" class="text-sm text-gray-600 underline">Cancelar</a>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
