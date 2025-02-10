<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Clientes') }}
            </h2>
            <a href="{{ route('clientes.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Novo cliente') }}
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

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <x-input-label for="busca" value="Buscar (empresa ou CNPJ)" />
                        <x-text-input id="busca" name="busca" value="{{ request('busca') }}" class="mt-1 block w-64" />
                    </div>
                    <div>
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" class="mt-1 block w-40 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="">Todos</option>
                            <option value="ativo" @selected(request('status') === 'ativo')>Ativo</option>
                            <option value="pausado" @selected(request('status') === 'pausado')>Pausado</option>
                            <option value="arquivado" @selected(request('status') === 'arquivado')>Arquivado</option>
                        </select>
                    </div>
                    <x-secondary-button type="submit">Filtrar</x-secondary-button>
                    @if (request('busca') || request('status'))
                        <a href="{{ route('clientes.index') }}" class="text-sm text-gray-500 underline">Limpar</a>
                    @endif
                </form>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Empresa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plano</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($clientes as $cliente)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $cliente->nome_empresa }}</div>
                                    <div class="text-sm text-gray-500">{{ $cliente->cnpj }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cliente->responsavel }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div>{{ $cliente->telefone }}</div>
                                    <div>{{ $cliente->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if ($cliente->plano?->personalizado)
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">Personalizado</span>
                                    @else
                                        {{ $cliente->plano->nome ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $cliente->status === 'ativo',
                                        'bg-yellow-100 text-yellow-800' => $cliente->status === 'pausado',
                                        'bg-gray-100 text-gray-800' => $cliente->status === 'arquivado',
                                    ])>
                                        {{ ucfirst($cliente->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('clientes.edit', $cliente) }}" title="Editar"
                                           class="p-1.5 rounded-md text-indigo-600 hover:bg-indigo-50 hover:text-indigo-900">
                                            <x-icon name="edit" />
                                        </a>

                                        <a href="{{ route('clientes.whatsapp.edit', $cliente) }}" title="WhatsApp Oficial"
                                           class="p-1.5 rounded-md text-emerald-600 hover:bg-emerald-50 hover:text-emerald-900">
                                            <x-icon name="message" />
                                        </a>

                                        <a href="{{ route('clientes.agentes.index', $cliente) }}" title="Agentes IA"
                                           class="p-1.5 rounded-md text-purple-600 hover:bg-purple-50 hover:text-purple-900">
                                            <x-icon name="cpu" />
                                        </a>

                                        <a href="{{ route('clientes.conversas.index', $cliente) }}" title="Conversas"
                                           class="p-1.5 rounded-md text-sky-600 hover:bg-sky-50 hover:text-sky-900">
                                            <x-icon name="chat" />
                                        </a>

                                        <a href="{{ route('clientes.leads.index', $cliente) }}" title="Leads"
                                           class="p-1.5 rounded-md text-orange-600 hover:bg-orange-50 hover:text-orange-900">
                                            <x-icon name="target" />
                                        </a>

                                        <a href="{{ route('clientes.contatos-bloqueados.index', $cliente) }}" title="Lista de bloqueio"
                                           class="p-1.5 rounded-md text-rose-600 hover:bg-rose-50 hover:text-rose-900">
                                            <x-icon name="archive" />
                                        </a>

                                        @if ($cliente->mensagens_proativas_habilitado)
                                            <a href="{{ route('clientes.campanhas.index', $cliente) }}" title="Campanhas"
                                               class="p-1.5 rounded-md text-fuchsia-600 hover:bg-fuchsia-50 hover:text-fuchsia-900">
                                                <x-icon name="send" />
                                            </a>
                                        @endif

                                        <a href="{{ route('clientes.financeiro.show', $cliente) }}" title="Financeiro"
                                           class="p-1.5 rounded-md text-teal-600 hover:bg-teal-50 hover:text-teal-900">
                                            <x-icon name="cash" />
                                        </a>

                                        <a href="{{ route('clientes.relatorio.show', $cliente) }}" title="Relatório"
                                           class="p-1.5 rounded-md text-violet-600 hover:bg-violet-50 hover:text-violet-900">
                                            <x-icon name="report" />
                                        </a>

                                        @if ($cliente->status !== 'ativo')
                                            <form action="{{ route('clientes.status', $cliente) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="ativo">
                                                <button type="submit" title="Ativar" class="p-1.5 rounded-md text-green-600 hover:bg-green-50 hover:text-green-900">
                                                    <x-icon name="check" />
                                                </button>
                                            </form>
                                        @endif

                                        @if ($cliente->status !== 'pausado')
                                            <form action="{{ route('clientes.status', $cliente) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="pausado">
                                                <button type="submit" title="Pausar" class="p-1.5 rounded-md text-yellow-600 hover:bg-yellow-50 hover:text-yellow-900">
                                                    <x-icon name="pause" />
                                                </button>
                                            </form>
                                        @endif

                                        @if ($cliente->status !== 'arquivado')
                                            <form action="{{ route('clientes.status', $cliente) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="arquivado">
                                                <button type="submit" title="Arquivar" class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-900">
                                                    <x-icon name="archive" />
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" onsubmit="return confirm('Excluir este cliente definitivamente?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" title="Excluir" class="p-1.5 rounded-md text-red-600 hover:bg-red-50 hover:text-red-900">
                                                <x-icon name="trash" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Nenhum cliente cadastrado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $clientes->links() }}
        </div>
    </div>
</x-app-layout>
