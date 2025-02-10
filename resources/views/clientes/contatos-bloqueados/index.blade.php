<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('clientes.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="back" class="w-5 h-5" />
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Lista de bloqueio — ') }}{{ $cliente->nome_empresa }}
                </h2>
            </div>
            <a href="{{ route('clientes.contatos-bloqueados.create', $cliente) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Novo contato') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <p class="text-sm text-gray-500">
                Números cadastrados aqui nunca recebem resposta automática da IA — use pra parentes,
                amigos ou qualquer contato pessoal que use o mesmo número comercial.
            </p>

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Importar em massa (CSV)</h3>
                <p class="text-xs text-gray-400 mb-3">
                    Arquivo .csv com colunas <code>telefone</code> (obrigatória) e <code>nome</code> (opcional).
                    Números já cadastrados são ignorados automaticamente.
                </p>
                <form method="POST" action="{{ route('clientes.contatos-bloqueados.importar', $cliente) }}" enctype="multipart/form-data" class="flex flex-wrap gap-3 items-center">
                    @csrf
                    <input type="file" name="arquivo" accept=".csv,text/csv" required class="block text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                    <x-secondary-button type="submit">Importar</x-secondary-button>
                </form>
                <x-input-error :messages="$errors->get('arquivo')" class="mt-2" />
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <x-input-label for="busca" value="Buscar (nome ou telefone)" />
                        <x-text-input id="busca" name="busca" value="{{ request('busca') }}" class="mt-1 block w-64" />
                    </div>
                    <x-secondary-button type="submit">Filtrar</x-secondary-button>
                    @if (request()->filled('busca'))
                        <a href="{{ route('clientes.contatos-bloqueados.index', $cliente) }}" class="text-sm text-gray-500 underline">Limpar</a>
                    @endif
                </form>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observações</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($contatos as $contato)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $contato->telefone }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $contato->nome ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $contato->observacoes }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('clientes.contatos-bloqueados.edit', [$cliente, $contato]) }}" title="Editar"
                                       class="p-1.5 rounded-md text-indigo-600 hover:bg-indigo-50 hover:text-indigo-900 inline-flex">
                                        <x-icon name="edit" />
                                    </a>
                                    <form action="{{ route('clientes.contatos-bloqueados.destroy', [$cliente, $contato]) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Remover {{ $contato->telefone }} da lista de bloqueio?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Remover" class="p-1.5 rounded-md text-red-600 hover:bg-red-50 hover:text-red-900 inline-flex">
                                            <x-icon name="trash" />
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                    Nenhum contato bloqueado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $contatos->links() }}
        </div>
    </div>
</x-app-layout>
