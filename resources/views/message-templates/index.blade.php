<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Templates de Mensagem') }}
            </h2>
            <a href="{{ route('message-templates.create') }}">
                <x-primary-button>Novo Template</x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-300 text-red-800 text-sm rounded-md p-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <x-input-label for="cliente_id" value="Cliente" />
                        <select id="cliente_id" name="cliente_id" class="mt-1 block w-56 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}" @selected(request('cliente_id') == $cliente->id)>{{ $cliente->nome_empresa }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-44 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            @foreach (\App\Models\MessageTemplate::statusLabels() as $valor => $label)
                                <option value="{{ $valor }}" @selected(request('status') === $valor)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <x-secondary-button type="submit">Filtrar</x-secondary-button>
                    @if (request()->anyFilled(['cliente_id', 'status']))
                        <a href="{{ route('message-templates.index') }}" class="text-sm text-gray-500 underline">Limpar</a>
                    @endif
                </form>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Idioma</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($templates as $template)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 font-mono">{{ $template->nome }}</div>
                                    <div class="text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($template->corpo, 60) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $template->cliente->nome_empresa ?? '—' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \App\Models\MessageTemplate::categoriaLabels()[$template->categoria] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $template->idioma }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $cores = [
                                            'rascunho' => 'bg-gray-100 text-gray-800',
                                            'pendente' => 'bg-amber-100 text-amber-800',
                                            'aprovado' => 'bg-green-100 text-green-800',
                                            'rejeitado' => 'bg-red-100 text-red-800',
                                            'pausado' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cores[$template->status] }}">
                                        {{ \App\Models\MessageTemplate::statusLabels()[$template->status] }}
                                    </span>
                                    @if ($template->status === 'rejeitado' && $template->motivo_rejeicao)
                                        <div class="text-xs text-red-600 mt-1">{{ $template->motivo_rejeicao }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    @if ($template->status === 'rascunho')
                                        <a href="{{ route('message-templates.edit', $template) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        <form method="POST" action="{{ route('message-templates.submeter', $template) }}" class="inline" onsubmit="return confirm('Submeter este template pra aprovação da Meta? Depois disso ele não pode mais ser editado por aqui.');">
                                            @csrf
                                            <button type="submit" class="text-emerald-600 hover:text-emerald-900">Submeter</button>
                                        </form>
                                        <form method="POST" action="{{ route('message-templates.destroy', $template) }}" class="inline" onsubmit="return confirm('Remover este rascunho?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Remover</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Nenhum template cadastrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $templates->links() }}
        </div>
    </div>
</x-app-layout>
