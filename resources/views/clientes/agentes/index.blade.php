<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="{{ route('clientes.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="back" class="w-5 h-5" />
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Agentes IA — ') }}{{ $cliente->nome_empresa }}
                </h2>
            </div>
            @if ($limiteAgentes['atingido'])
                <span title="Limite de agentes do plano atingido — faça upgrade de plano para adicionar mais."
                      class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-400 uppercase tracking-widest cursor-not-allowed">
                    {{ __('Novo agente') }}
                </span>
            @else
                <a href="{{ route('clientes.agentes.create', $cliente) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Novo agente') }}
                </a>
            @endif
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

            <div class="text-sm text-gray-500">
                {{ $limiteAgentes['atual'] }} agente(s) {{ $limiteAgentes['limite'] === null ? '(ilimitado no plano atual)' : 'de '.$limiteAgentes['limite'].' permitido(s) pelo plano '.($cliente->plano->nome ?? '') }}
            </div>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temperatura</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horário</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($agentes as $agente)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        @if ($agente->avatar)
                                            <img src="{{ asset('storage/' . $agente->avatar) }}" class="w-8 h-8 rounded-full object-cover border border-gray-200" alt="">
                                        @else
                                            <span class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: {{ $agente->cor ?? '#6366f1' }}">
                                                <x-icon name="headset" class="w-4 h-4 text-white" />
                                            </span>
                                        @endif
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $agente->nome }}</div>
                                            <div class="text-sm text-gray-500 truncate max-w-xs" title="{{ $agente->objetivo }}">{{ $agente->objetivo }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $agente->modelo }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $agente->temperatura }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $diasAbertos = $agente->horarios->where('fechado', false)->count();
                                    @endphp
                                    @if ($agente->horarios->isEmpty())
                                        <span class="text-gray-400">Não configurado</span>
                                    @elseif ($diasAbertos === 7)
                                        Todos os dias
                                    @elseif ($diasAbertos === 0)
                                        Fechado
                                    @else
                                        {{ $diasAbertos }} {{ Str::plural('dia', $diasAbertos) }}/semana
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span @class([
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        'bg-green-100 text-green-800' => $agente->ativo,
                                        'bg-gray-100 text-gray-800' => ! $agente->ativo,
                                    ])>
                                        {{ $agente->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('clientes.agentes.edit', [$cliente, $agente]) }}" title="Editar"
                                           class="p-1.5 rounded-md text-indigo-600 hover:bg-indigo-50 hover:text-indigo-900">
                                            <x-icon name="edit" />
                                        </a>

                                        <form action="{{ route('clientes.agentes.toggle', [$cliente, $agente]) }}" method="POST">
                                            @csrf @method('PATCH')
                                            <button type="submit" title="{{ $agente->ativo ? 'Desativar' : 'Ativar' }}"
                                                    class="p-1.5 rounded-md {{ $agente->ativo ? 'text-yellow-600 hover:bg-yellow-50 hover:text-yellow-900' : 'text-green-600 hover:bg-green-50 hover:text-green-900' }}">
                                                <x-icon name="{{ $agente->ativo ? 'pause' : 'check' }}" />
                                            </button>
                                        </form>

                                        <form action="{{ route('clientes.agentes.destroy', [$cliente, $agente]) }}" method="POST" onsubmit="return confirm('Excluir este agente definitivamente?');">
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
                                    Nenhum agente cadastrado para este cliente ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
