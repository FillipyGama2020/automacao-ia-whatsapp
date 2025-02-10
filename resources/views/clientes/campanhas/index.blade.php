<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('clientes.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="back" class="w-5 h-5" />
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Campanhas — ') }}{{ $cliente->nome_empresa }}
                </h2>
            </div>
            @if ($cliente->mensagens_proativas_habilitado)
                <a href="{{ route('clientes.campanhas.create', $cliente) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Nova campanha') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-4">

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

            @unless ($cliente->mensagens_proativas_habilitado)
                <div class="bg-amber-50 border border-amber-300 rounded-md p-4 text-sm text-amber-800">
                    Mensagens proativas não estão habilitadas pra este cliente — habilite na edição do cadastro dele antes de criar uma campanha.
                </div>
            @endunless

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinatários</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criada em</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($campanhas as $campanha)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('clientes.campanhas.show', [$cliente, $campanha]) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">
                                        {{ $campanha->messageTemplate->nome }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $campanha->total_leads }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($campanha->valor_cobrado, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ \App\Models\Campanha::statusLabels()[$campanha->status] }}
                                    </span>
                                    @if ($campanha->status === 'agendada')
                                        <div class="text-xs text-gray-400 mt-1">{{ $campanha->agendado_para->format('d/m/Y H:i') }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $campanha->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Nenhuma campanha criada ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $campanhas->links() }}
        </div>
    </div>
</x-app-layout>
