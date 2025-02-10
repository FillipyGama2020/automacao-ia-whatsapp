@csrf

<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    <div>
        <x-input-label for="nome" value="Nome" />
        <x-text-input id="nome" name="nome" class="mt-1 block w-full" value="{{ old('nome', $lead->nome ?? '') }}" autofocus />
        <x-input-error :messages="$errors->get('nome')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="telefone" value="Telefone *" />
        <x-text-input id="telefone" name="telefone" class="mt-1 block w-full" value="{{ old('telefone', $lead->telefone ?? '') }}" required />
        <x-input-error :messages="$errors->get('telefone')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" type="email" name="email" class="mt-1 block w-full" value="{{ old('email', $lead->email ?? '') }}" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="classificacao" value="Classificação" />
        <select id="classificacao" name="classificacao" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">Não classificado</option>
            @foreach (\App\Models\Lead::classificacaoLabels() as $valor => $label)
                <option value="{{ $valor }}" @selected(old('classificacao', $lead->classificacao ?? '') === $valor)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('classificacao')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="status" value="Status *" />
        <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
            @foreach (\App\Models\Lead::statusLabels() as $valor => $label)
                <option value="{{ $valor }}" @selected(old('status', $lead->status ?? 'novo') === $valor)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" class="mt-2" />
    </div>

    @if ($lead->exists)
        <div>
            <x-input-label value="Origem" />
            <p class="mt-1 text-sm text-gray-600 py-2">{{ \App\Models\Lead::origemLabels()[$lead->origem] }}</p>
        </div>
    @endif

    <div class="sm:col-span-2">
        <label class="inline-flex items-center gap-2">
            <input type="hidden" name="aceita_campanhas" value="0">
            <input type="checkbox" name="aceita_campanhas" value="1" @checked(old('aceita_campanhas', $lead->aceita_campanhas ?? true)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="text-sm text-gray-700">Aceita receber campanhas</span>
        </label>
        <p class="mt-1 text-xs text-gray-500">
            Desmarcar impede que este lead entre em qualquer campanha proativa — use pra um pedido explícito de "não quero mais receber mensagens" que não veio pela detecção automática.
            @if (($lead->opt_out_em ?? null))
                Marcado como opt-out em {{ $lead->opt_out_em->format('d/m/Y H:i') }}.
            @endif
        </p>
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="interesse" value="Interesse" />
        <textarea id="interesse" name="interesse" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('interesse', $lead->interesse ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('interesse')" class="mt-2" />
    </div>

    <div class="sm:col-span-2">
        <x-input-label for="observacoes" value="Observações" />
        <textarea id="observacoes" name="observacoes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('observacoes', $lead->observacoes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('observacoes')" class="mt-2" />
    </div>

    @if ($lead->exists && $lead->conversa)
        <div class="sm:col-span-2">
            <x-input-label value="Conversa de origem" />
            <a href="{{ route('clientes.conversas.show', [$cliente, $lead->conversa]) }}" class="mt-1 inline-block text-sm text-indigo-600 hover:text-indigo-900 underline">
                Ver conversa #{{ $lead->conversa->id }} — {{ $lead->conversa->iniciada_em->format('d/m/Y H:i') }}
            </a>
        </div>
    @endif
</div>

<div class="flex items-center gap-4 mt-6">
    <x-primary-button>{{ __('Salvar') }}</x-primary-button>
    <a href="{{ route('clientes.leads.index', $cliente) }}" class="text-sm text-gray-600 underline">Cancelar</a>
</div>
