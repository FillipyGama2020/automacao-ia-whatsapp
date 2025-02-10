<div class="border-b border-gray-200">
    <nav class="-mb-px flex gap-6">
        <a href="{{ route('financeiro.dashboard') }}"
           class="border-b-2 pb-3 px-1 text-sm font-medium {{ request()->routeIs('financeiro.dashboard') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Dashboard
        </a>
        <a href="{{ route('financeiro.fechamentos.index') }}"
           class="border-b-2 pb-3 px-1 text-sm font-medium {{ request()->routeIs('financeiro.fechamentos.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Fechamentos
        </a>
        <a href="{{ route('custos-infraestrutura.index') }}"
           class="border-b-2 pb-3 px-1 text-sm font-medium {{ request()->routeIs('custos-infraestrutura.*') ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
            Custos de Infraestrutura
        </a>
    </nav>
</div>
