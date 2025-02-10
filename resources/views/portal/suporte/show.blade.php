<x-portal-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('portal.suporte.index') }}" class="text-gray-400 hover:text-gray-600">
                <x-icon name="back" class="w-5 h-5" />
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $ticket->assunto }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('status'))
                <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                @foreach ($ticket->mensagens as $mensagem)
                    @php($ehCliente = $mensagem->remetente === 'cliente')
                    <div class="flex {{ $ehCliente ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%]">
                            <div class="text-xs text-gray-400 mb-1 {{ $ehCliente ? 'text-right' : 'text-left' }}">
                                {{ $ehCliente ? 'Você' : 'Suporte' }} · {{ $mensagem->created_at->format('d/m H:i') }}
                            </div>
                            <div class="rounded-lg px-4 py-2 text-sm {{ $ehCliente ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                                {{ $mensagem->mensagem }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($ticket->status !== 'fechado')
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <form method="POST" action="{{ route('portal.suporte.responder', $ticket) }}">
                        @csrf
                        <textarea name="mensagem" rows="3" class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="Escreva sua resposta..." required></textarea>
                        <x-input-error :messages="$errors->get('mensagem')" class="mt-2" />
                        <x-primary-button class="mt-3">{{ __('Responder') }}</x-primary-button>
                    </form>
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 text-gray-500 text-sm rounded-md p-4 text-center">
                    Este chamado está fechado.
                </div>
            @endif
        </div>
    </div>
</x-portal-layout>
