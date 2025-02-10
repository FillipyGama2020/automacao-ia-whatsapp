<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('clientes.conversas.index', $cliente) }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $conversa->contato_nome ?? 'Sem nome' }} — {{ $conversa->contato_telefone }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @php
                $cores = [
                    'em_andamento' => 'bg-blue-100 text-blue-800',
                    'resolvida_ia' => 'bg-green-100 text-green-800',
                    'transferida_humano' => 'bg-amber-100 text-amber-800',
                    'abandonada' => 'bg-gray-100 text-gray-800',
                ];
            @endphp
            <div class="bg-white shadow-sm sm:rounded-lg" x-data="{ resumoAberto: false }">
                <button type="button" @click="resumoAberto = !resumoAberto" class="w-full flex items-center justify-between p-4 sm:p-6 text-left">
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-medium text-gray-700">Detalhes da conversa</span>
                        <span class="inline-flex px-2 text-xs leading-5 font-semibold rounded-full {{ $cores[$conversa->status] }}">
                            {{ \App\Models\Conversa::statusLabels()[$conversa->status] }}
                        </span>
                    </div>
                    <x-icon name="chevron-down" class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="{ 'rotate-180': resumoAberto }" />
                </button>

                <div x-show="resumoAberto" x-cloak class="px-4 sm:px-6 pb-6 pt-2 border-t border-gray-100">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <div class="text-gray-400 text-xs uppercase tracking-wider">Status</div>
                            <span class="mt-1 inline-flex px-2 text-xs leading-5 font-semibold rounded-full {{ $cores[$conversa->status] }}">
                                {{ \App\Models\Conversa::statusLabels()[$conversa->status] }}
                            </span>
                        </div>

                        <div>
                            <div class="text-gray-400 text-xs uppercase tracking-wider">Agente</div>
                            <div class="mt-1 text-gray-700">{{ $conversa->agente->nome ?? '—' }}</div>
                        </div>

                        <div>
                            <div class="text-gray-400 text-xs uppercase tracking-wider">Custo estimado</div>
                            <div class="mt-1 text-gray-700">R$ {{ number_format($conversa->custo_estimado, 4, ',', '.') }}</div>
                        </div>

                        <div>
                            <div class="text-gray-400 text-xs uppercase tracking-wider">Tokens</div>
                            <div class="mt-1 text-gray-700">{{ $conversa->tokens_prompt_total }} + {{ $conversa->tokens_resposta_total }}</div>
                        </div>

                        <div>
                            <div class="text-gray-400 text-xs uppercase tracking-wider">Iniciada em</div>
                            <div class="mt-1 text-gray-700">{{ $conversa->iniciada_em->format('d/m/Y H:i') }}</div>
                        </div>

                        <div>
                            <div class="text-gray-400 text-xs uppercase tracking-wider">Última atividade</div>
                            <div class="mt-1 text-gray-700">{{ $conversa->ultima_mensagem_em?->format('d/m/Y H:i') ?? '—' }}</div>
                        </div>

                        <div>
                            <div class="text-gray-400 text-xs uppercase tracking-wider">Avaliação</div>
                            <div class="mt-1 text-gray-700">{{ $conversa->avaliacao ? '★ '.$conversa->avaliacao.'/5' : '—' }}</div>
                        </div>

                        @if ($conversa->status === 'transferida_humano')
                            <div>
                                <div class="text-gray-400 text-xs uppercase tracking-wider">Motivo da transferência</div>
                                <div class="mt-1 text-gray-700">{{ $conversa->motivo_transferencia ?? '—' }}</div>
                            </div>
                        @endif
                    </div>

                    @unless ($conversa->avaliada_em)
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="text-gray-400 text-xs uppercase tracking-wider mb-1">Link de avaliação (enviar ao contato)</div>
                            <input type="text" readonly value="{{ $conversa->linkAvaliacao() }}" onclick="this.select()" class="block w-full font-mono text-xs border-gray-300 rounded-md bg-gray-50 text-gray-600">
                        </div>
                    @endunless
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
              <div class="max-h-[65vh] overflow-y-auto space-y-4" x-data x-init="$el.scrollTop = $el.scrollHeight">
                @forelse ($conversa->mensagens as $mensagem)
                    @if ($mensagem->remetente === 'sistema')
                        <div class="text-center text-xs text-gray-400 py-1">{{ $mensagem->conteudo }}</div>
                    @else
                        @php
                            $ehContato = $mensagem->remetente === 'contato';
                            $bolhaCores = [
                                'contato' => 'bg-gray-100 text-gray-800',
                                'agente_ia' => 'bg-indigo-600 text-white',
                                'humano' => 'bg-emerald-600 text-white',
                                'campanha' => 'bg-fuchsia-600 text-white',
                            ];
                            $rotulos = ['contato' => 'Contato', 'agente_ia' => 'Agente IA', 'humano' => 'Atendente', 'campanha' => 'Campanha'];
                        @endphp
                        <div class="flex {{ $ehContato ? 'justify-start' : 'justify-end' }}">
                            <div class="max-w-[75%]">
                                <div class="text-xs text-gray-400 mb-1 {{ $ehContato ? 'text-left' : 'text-right' }}">
                                    {{ $rotulos[$mensagem->remetente] }} · {{ $mensagem->enviada_em->format('d/m H:i') }}
                                </div>
                                <div class="rounded-lg px-4 py-2 text-sm {{ $bolhaCores[$mensagem->remetente] }} space-y-2">
                                    @if ($mensagem->midia_path)
                                        @if ($mensagem->tipo === 'imagem')
                                            <img src="{{ asset('storage/'.$mensagem->midia_path) }}" class="max-w-full rounded-md max-h-64">
                                        @elseif ($mensagem->tipo === 'audio')
                                            <audio controls src="{{ asset('storage/'.$mensagem->midia_path) }}" class="max-w-full"></audio>
                                        @elseif ($mensagem->tipo === 'video')
                                            <video controls src="{{ asset('storage/'.$mensagem->midia_path) }}" class="max-w-full rounded-md max-h-64"></video>
                                        @else
                                            <a href="{{ asset('storage/'.$mensagem->midia_path) }}" target="_blank" class="underline flex items-center gap-1">
                                                <x-icon name="chat" class="w-4 h-4" /> Abrir documento
                                            </a>
                                        @endif
                                    @endif
                                    @if ($mensagem->conteudo)
                                        <div>{{ $mensagem->conteudo }}</div>
                                    @elseif (! $mensagem->midia_path)
                                        <span class="italic opacity-75">[{{ ucfirst($mensagem->tipo) }}]</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 mt-1 {{ $ehContato ? 'text-left' : 'text-right' }}">
                                    @if ($mensagem->remetente === 'agente_ia' && $mensagem->tokens_prompt)
                                        {{ $mensagem->tokens_prompt + $mensagem->tokens_resposta }} tokens
                                    @endif
                                    @if ($mensagem->status_entrega)
                                        · {{ ucfirst($mensagem->status_entrega) }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-sm text-gray-500 text-center py-8">Nenhuma mensagem registrada nesta conversa.</p>
                @endforelse
              </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if (session('error'))
                    <div class="bg-red-100 border border-red-300 text-red-800 text-sm rounded-md p-3 mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('clientes.conversas.responder', [$cliente, $conversa]) }}">
                    @csrf
                    <x-input-label for="mensagem" value="Responder pelo painel" />
                    <textarea id="mensagem" name="mensagem" rows="3" required class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Escreva a mensagem para enviar ao contato pelo WhatsApp..."></textarea>
                    <x-input-error :messages="$errors->get('mensagem')" class="mt-2" />
                    <p class="text-xs text-gray-400 mt-1">Ao enviar, você assume a conversa manualmente — a IA para de responder até você devolver o atendimento a ela.</p>

                    <div class="flex items-center gap-3 mt-3">
                        <x-primary-button type="submit">Enviar e assumir conversa</x-primary-button>
                    </div>
                </form>

                @if ($conversa->status === 'transferida_humano')
                    <form method="POST" action="{{ route('clientes.conversas.retomar-ia', [$cliente, $conversa]) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="text-sm text-gray-600 underline">Devolver atendimento para a IA</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
