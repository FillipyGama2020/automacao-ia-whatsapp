<x-guest-layout>
    <div class="text-center">
        <h1 class="text-lg font-semibold text-gray-800">
            {{ __('Como foi seu atendimento') }}{{ $conversa->cliente ? ' com '.$conversa->cliente->nome_empresa : '' }}?
        </h1>

        @if ($conversa->avaliada_em)
            <p class="mt-4 text-sm text-gray-600">Obrigado! Você avaliou este atendimento com nota <strong>{{ $conversa->avaliacao }}/5</strong>.</p>
        @else
            <p class="mt-2 text-sm text-gray-500">Sua opinião nos ajuda a melhorar.</p>

            <form method="POST" action="{{ route('avaliacao.store', $conversa->token_avaliacao) }}" class="mt-6">
                @csrf
                <div class="flex justify-center gap-2">
                    @for ($nota = 1; $nota <= 5; $nota++)
                        <button type="submit" name="avaliacao" value="{{ $nota }}"
                                class="w-12 h-12 rounded-full border border-gray-300 text-lg font-semibold text-gray-600 hover:bg-indigo-50 hover:border-indigo-400 hover:text-indigo-600">
                            {{ $nota }}
                        </button>
                    @endfor
                </div>
                <div class="flex justify-between text-xs text-gray-400 mt-2 px-1">
                    <span>Muito ruim</span>
                    <span>Excelente</span>
                </div>
                <x-input-error :messages="$errors->get('avaliacao')" class="mt-4" />
            </form>
        @endif
    </div>
</x-guest-layout>
