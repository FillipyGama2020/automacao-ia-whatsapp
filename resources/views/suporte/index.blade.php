<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suporte — Chamados') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-4">
                <form method="GET" class="flex flex-wrap gap-3 items-end">
                    <div>
                        <x-input-label for="cliente_id" value="Cliente" />
                        <select id="cliente_id" name="cliente_id" class="mt-1 block w-48 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
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
                            @foreach (\App\Models\SuporteTicket::statusLabels() as $valor => $label)
                                <option value="{{ $valor }}" @selected(request('status') === $valor)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-secondary-button type="submit">Filtrar</x-secondary-button>
                    @if (request()->anyFilled(['cliente_id', 'status']))
                        <a href="{{ route('suporte.index') }}" class="text-sm text-gray-500 underline">Limpar</a>
                    @endif
                </form>
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assunto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atualizado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($tickets as $ticket)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('suporte.show', $ticket) }}'">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $ticket->cliente->nome_empresa }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-indigo-600 hover:underline">{{ $ticket->assunto }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $cores = ['aberto' => 'bg-blue-100 text-blue-800', 'respondido' => 'bg-green-100 text-green-800', 'fechado' => 'bg-gray-100 text-gray-800'];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $cores[$ticket->status] }}">
                                        {{ \App\Models\SuporteTicket::statusLabels()[$ticket->status] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ticket->updated_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">Nenhum chamado registrado ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $tickets->links() }}
        </div>
    </div>
</x-app-layout>
