<x-portal-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Suporte') }}
            </h2>
            <a href="{{ route('portal.suporte.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                {{ __('Abrir chamado') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assunto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atualizado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($tickets as $ticket)
                            <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('portal.suporte.show', $ticket) }}'">
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
                                <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">Nenhum chamado aberto ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $tickets->links() }}
        </div>
    </div>
</x-portal-layout>
