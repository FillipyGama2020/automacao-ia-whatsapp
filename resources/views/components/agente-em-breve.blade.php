@props(['titulo', 'itens' => []])

<div class="text-center py-12">
    <div class="mx-auto w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center mb-4">
        <x-icon name="cpu" class="w-6 h-6 text-indigo-400" />
    </div>
    <h3 class="text-sm font-medium text-gray-700">{{ $titulo }}</h3>
    <p class="text-sm text-gray-400 mt-1">Em breve — esta aba será construída numa próxima etapa.</p>
    @if (count($itens))
        <ul class="mt-4 text-xs text-gray-400 space-y-1 inline-block text-left">
            @foreach ($itens as $item)
                <li>• {{ $item }}</li>
            @endforeach
        </ul>
    @endif
</div>
