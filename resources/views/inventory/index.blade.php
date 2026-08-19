@extends('layouts.admin')

@section('title', 'Inventario')

@section('content')
    <div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

        <!-- Hero / Action Area -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="package" class="w-8 h-8 text-violet-500"></i> Stock <span
                        class="text-violet-gradient tracking-tighter">Real-Time</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Control total de equipos, modelos, specs y
                    condiciones.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative group w-full md:w-auto">
                    <form action="{{ route('inventory.index') }}" method="GET" id="searchForm">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <i data-lucide="search"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-indigo-500 transition-colors pointer-events-none"></i>
                        <input name="search" value="{{ request('search') }}" type="text" id="searchInput"
                            placeholder="Buscar IMEI, Modelo..."
                            class="pl-11 pr-10 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-indigo-500/10 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all w-full md:w-72 shadow-sm text-sm font-medium">

                        @if(request('search'))
                            <a href="{{ route('inventory.index', request()->except(['search', 'page'])) }}"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 transition-colors"
                                title="Borrar búsqueda">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </a>
                        @endif
                    </form>
                </div>
                <a href="{{ route('inventory.import.index') }}"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-2xl shadow-xl shadow-emerald-500/20 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-3 group hidden md:flex">
                    <i data-lucide="file-spreadsheet" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                    Importar Excel
                </a>
                <a href="{{ route('inventory.create') }}"
                    class="btn-violet px-8 py-3 rounded-2xl shadow-xl border border-white/10 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-3 group">
                    <i data-lucide="scan-barcode" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                    Agregar Stock
                </a>
            </div>
        </div>

        <!-- Quick Filters & Branch -->
        <div class="flex flex-wrap items-center gap-2 pb-2">
            <a href="{{ route('inventory.index', request()->except(['status', 'page'])) }}"
                class="px-5 py-2 rounded-xl text-xs uppercase tracking-tighter whitespace-nowrap transition-colors border {{ request('status') ? 'bg-white dark:bg-dark-alt text-gray-500 hover:bg-violet-50 dark:hover:bg-white/5 font-bold border-gray-100 dark:border-white/5' : 'btn-violet border-transparent' }}">Todos
                los equipos</a>
            <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['status' => 'in_stock'])) }}"
                class="px-5 py-2 rounded-xl text-xs uppercase tracking-tighter whitespace-nowrap transition-colors border {{ request('status') === 'in_stock' ? 'btn-violet border-transparent' : 'bg-white dark:bg-dark-alt text-gray-500 hover:bg-violet-50 dark:hover:bg-white/5 font-bold border-gray-100 dark:border-white/5' }}">En
                Stock</a>
            <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['status' => 'reserved'])) }}"
                class="px-5 py-2 rounded-xl text-xs uppercase tracking-tighter whitespace-nowrap transition-colors border {{ request('status') === 'reserved' ? 'btn-violet border-transparent' : 'bg-white dark:bg-dark-alt text-gray-500 hover:bg-violet-50 dark:hover:bg-white/5 font-bold border-gray-100 dark:border-white/5' }}">Apartados</a>
            <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['status' => 'sold'])) }}"
                class="px-5 py-2 rounded-xl text-xs uppercase tracking-tighter whitespace-nowrap transition-colors border {{ request('status') === 'sold' ? 'btn-violet border-transparent' : 'bg-white dark:bg-dark-alt text-gray-500 hover:bg-violet-50 dark:hover:bg-white/5 font-bold border-gray-100 dark:border-white/5' }}">Vendido</a>

            <div class="h-4 w-[1px] bg-gray-200 dark:bg-white/10 mx-2"></div>

            <a href="{{ route('inventory.index', request()->except(['category', 'page'])) }}"
                class="px-5 py-2 rounded-xl text-xs uppercase tracking-tighter whitespace-nowrap transition-colors border {{ !request('category') ? 'bg-indigo-600 text-white border-transparent shadow-lg shadow-indigo-500/20' : 'bg-white dark:bg-dark-alt text-gray-500 hover:bg-indigo-50 dark:hover:bg-white/5 font-bold border-gray-100 dark:border-white/5' }}">Todos</a>
            <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['category' => 'iphone'])) }}"
                class="px-5 py-2 rounded-xl text-xs uppercase tracking-tighter whitespace-nowrap transition-colors border {{ request('category') === 'iphone' ? 'bg-indigo-600 text-white border-transparent shadow-lg shadow-indigo-500/20' : 'bg-white dark:bg-dark-alt text-gray-500 hover:bg-indigo-50 dark:hover:bg-white/5 font-bold border-gray-100 dark:border-white/5' }}">📱 Equipos</a>
            <a href="{{ route('inventory.index', array_merge(request()->except('page'), ['category' => 'accessory'])) }}"
                class="px-5 py-2 rounded-xl text-xs uppercase tracking-tighter whitespace-nowrap transition-colors border {{ request('category') === 'accessory' ? 'bg-indigo-600 text-white border-transparent shadow-lg shadow-indigo-500/20' : 'bg-white dark:bg-dark-alt text-gray-500 hover:bg-indigo-50 dark:hover:bg-white/5 font-bold border-gray-100 dark:border-white/5' }}">🎧 Accesorios</a>

            @if(isset($branches) && $branches->count() > 0)
                <div class="ml-auto">
                    <form action="{{ route('inventory.index') }}" method="GET" id="branchFilter">
                        @foreach(request()->except(['branch_id','page']) as $key => $val)
                            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                        @endforeach
                        <select name="branch_id" onchange="document.getElementById('branchFilter').submit()"
                            class="pl-4 pr-8 py-2 rounded-xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none text-xs font-black appearance-none cursor-pointer focus:border-violet-500 transition-colors">
                            <option value="">📍 Todas las sucursales</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endif
        </div>

        <!-- Main Table Container -->
        <div
            class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-indigo-500/5 dark:shadow-none overflow-hidden relative group">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr
                            class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Modelo / Specs</th>
                            <th class="px-8 py-5 text-left hidden md:table-cell">Condición / Batería</th>
                            <th class="px-8 py-5 text-left hidden lg:table-cell">Identificadores</th>
                            <th class="px-8 py-5 text-left">Precio Venta</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Estado</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    @forelse($items as $group)
                        @php $firstItem = $group->first(); @endphp
                        <tbody
                            class="divide-y divide-gray-50 dark:divide-white/5 border-b border-gray-50 dark:border-white/5 last:border-0"
                            x-data="{ expanded: false }">
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all group overflow-hidden cursor-pointer"
                                @click="expanded = !expanded">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-white/5 flex items-center justify-center border border-indigo-100 dark:border-white/10 shadow-sm relative transition-transform overflow-hidden"
                                            :class="expanded ? 'scale-105' : 'group-hover:scale-105'">
                                            <i data-lucide="{{ $firstItem->category === 'accessory' ? 'headphones' : 'smartphone' }}" class="w-7 h-7 text-indigo-500 transition-opacity"
                                                :class="expanded ? 'opacity-20' : 'group-hover:opacity-20'"></i>
                                            <div class="absolute inset-0 flex items-center justify-center transition-opacity"
                                                :class="expanded ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'">
                                                <i data-lucide="chevron-down"
                                                    class="w-5 h-5 text-indigo-600 transition-transform"
                                                    :class="expanded ? 'rotate-180' : ''"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <p
                                                class="text-base font-black text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 transition-colors tracking-tight">
                                                {{ $firstItem->brand }} {{ $firstItem->model }}
                                            </p>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                                                @if($firstItem->category !== 'accessory')
                                                    {{ $firstItem->storage }} | 
                                                @endif
                                                {{ $firstItem->color }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden md:table-cell">
                                    <span
                                        class="text-xs font-black uppercase text-indigo-600 px-3 py-1 rounded-xl bg-indigo-500/10 border border-indigo-500/20">
                                        {{ $group->count() }} {{ $group->count() === 1 ? 'Unidad' : 'Unidades' }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 hidden lg:table-cell">
                                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                                        Ocultos (Ver Detalle)
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-left">
                                        <p class="text-lg font-black text-violet-gradient tracking-tighter">$
                                            {{ number_format($firstItem->sale_price, 2) }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden sm:table-cell">
                                    <span
                                        class="text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-600 px-4 py-1.5 rounded-full border border-emerald-500/20 shadow-sm shadow-emerald-500/5">
                                        {{ $firstItem->status->value }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <button
                                        class="p-3 hover:bg-violet-600 text-gray-400 hover:text-white transition-all rounded-2xl border border-transparent shadow-sm"
                                        :class="expanded ? 'btn-violet text-white' : 'bg-gray-50 dark:bg-dark'">
                                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform"
                                            :class="expanded ? 'rotate-180' : ''"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Detalles de las Unidades -->
                            <tr class="bg-gray-50/30 dark:bg-white-[0.02] border-b-0" x-show="expanded" x-collapse x-cloak>
                                <td colspan="6" class="p-0 border-b border-gray-100 dark:border-white/5">
                                    <div class="px-8 py-6 bg-white dark:bg-dark-alt shadow-inner">
                                        <h4
                                            class="text-[10px] font-black uppercase tracking-widest text-violet-500 mb-4 flex items-center gap-2 italic">
                                            <i data-lucide="layers" class="w-3 h-3"></i> Listado de Unidades Individuales
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($group as $item)
                                                <div
                                                    class="flex flex-col p-4 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5 hover:border-indigo-500/30 hover:shadow-lg hover:shadow-indigo-500/5 transition-all animate-in fade-in slide-in-from-top-2 duration-300">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <div class="flex items-center gap-2">
                                                            <span
                                                                class="text-[10px] font-black uppercase bg-indigo-500/10 text-indigo-600 px-2 py-0.5 rounded-lg border border-indigo-500/20">
                                                                {{ $item->cosmetic_condition }}
                                                            </span>
                                                                @if($item->category !== 'accessory')
                                                                    <div class="flex items-center text-[10px] font-bold text-emerald-500">
                                                                        <i data-lucide="battery-full" class="w-3 h-3 mr-1"></i>
                                                                        {{ $item->battery_health }}%
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        <div class="flex items-center gap-1">
                                                            <a href="{{ route('inventory.edit', $item) }}"
                                                                class="p-1.5 text-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 rounded-xl transition-all">
                                                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                            </a>
                                                            <form id="delete-form-{{ $item->id }}" action="{{ route('inventory.destroy', $item) }}" method="POST" class="inline">
                                                                @csrf @method('DELETE')
                                                                <button type="button" @click="$dispatch('open-delete-modal', { id: 'delete-form-{{ $item->id }}', name: '{{ $item->brand }} {{ $item->model }} (SN: {{ $item->serial_number ?? $item->imei ?? 'N/A' }})' })"
                                                                    class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all" title="Eliminar">
                                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <div class="space-y-1">
                                                        @if($item->category !== 'accessory')
                                                            <p
                                                                class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter">
                                                                <span class="text-gray-400 dark:text-gray-500/50">IMEI:</span>
                                                                {{ $item->imei ?? 'N/A' }}
                                                            </p>
                                                        @endif
                                                        <p
                                                            class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter">
                                                            <span class="text-gray-400 dark:text-gray-500/50">SN:</span>
                                                            {{ $item->serial_number ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                    <div
                                                        class="mt-3 pt-3 border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                                                        <p
                                                            class="text-[10px] text-gray-400 font-bold uppercase tracking-widest italic">
                                                            Coste
                                                        </p>
                                                        <p class="text-xs font-black text-gray-900 dark:text-gray-100">
                                                            ${{ number_format($item->purchase_price, 2) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @empty
                        <tbody>
                            <tr>
                                <td colspan="6" class="py-32 text-center relative">
                                    <div class="absolute inset-0 bg-indigo-50/20 dark:bg-transparent pointer-events-none"></div>
                                    <div class="flex flex-col items-center justify-center text-gray-400 relative z-10">
                                        <div
                                            class="w-20 h-20 bg-indigo-500/5 rounded-full flex items-center justify-center mb-6 animate-pulse">
                                            <i data-lucide="box-select" class="w-10 h-10 text-indigo-300"></i>
                                        </div>
                                        <h3 class="text-xl font-black text-gray-400 tracking-tight italic">No hay stock
                                            registrado</h3>
                                        <p class="text-xs font-bold mt-2 uppercase tracking-[0.3em] opacity-40">Empezá agregando
                                            tu primer producto</p>
                                        <a href="{{ route('inventory.import.index') }}"
                                            class="mt-8 bg-indigo-600/10 text-indigo-600 hover:bg-indigo-600 hover:text-white px-6 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">Importar
                                            desde Excel</a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    @endforelse
                </table>
            </div>

            <div
                class="px-8 py-6 bg-gray-50/50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 items-center justify-between flex">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Mostrando
                    {{ $items->firstItem() ?? 0 }} a {{ $items->lastItem() ?? 0 }} de {{ $items->total() }} variantes
                </p>
                <div class="pagination-custom">
                    {{ $items->links() }}
                </div>
            </div>
        </div>

    </div>

    <!-- Alpine.js Delete Modal Component -->
    <div x-data="{ 
            open: false, 
            formId: null,
            itemName: '',
            confirmDelete(id, name) {
                this.formId = id;
                this.itemName = name;
                this.open = true;
            },
            submitForm() {
                if (this.formId) {
                    document.getElementById(this.formId).submit();
                }
            }
        }" 
        @open-delete-modal.window="confirmDelete($event.detail.id, $event.detail.name)"
        class="relative z-[200]" 
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true" 
        x-cloak>
        
        <div x-show="open" 
            x-transition:enter="ease-out duration-300" 
            x-transition:enter-start="opacity-0" 
            x-transition:enter-end="opacity-100" 
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-dark/80 backdrop-blur-sm transition-opacity"></div>
        
        <div x-show="open" class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div @click.away="open = false" 
                    x-transition:enter="ease-out duration-300 transform" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave="ease-in duration-200 transform"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-[30px] bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md">
                    
                    <!-- Modal Body -->
                    <div class="px-6 pb-6 pt-8 sm:p-8 sm:pb-6 flex flex-col items-center">
                        <div class="mx-auto flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-2xl bg-red-50 dark:bg-red-500/10 mb-5 relative group border border-red-100 dark:border-red-500/20">
                            <!-- Ping animation -->
                            <div class="absolute inset-0 rounded-2xl bg-red-400 opacity-20 group-hover:animate-ping"></div>
                            <svg class="h-8 w-8 text-red-500 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </div>
                        <div class="text-center w-full">
                            <h3 class="text-xl font-black leading-6 text-gray-900 dark:text-gray-100 tracking-tight" id="modal-title">¿Eliminar del Inventario?</h3>
                            <div class="mt-3 text-sm text-gray-500 dark:text-gray-400 font-medium">
                                <p>Esta acción removerá el siguiente equipo a modo de Soft-Delete temporal:</p>
                                <div class="mt-4 px-4 py-3 bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5 rounded-2xl flex items-center justify-center gap-2">
                                    <i data-lucide="smartphone" class="w-4 h-4 text-gray-400"></i>
                                    <span class="text-xs font-black uppercase tracking-widest text-gray-700 dark:text-gray-300" x-text="itemName"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Actions -->
                    <div class="px-6 py-5 sm:flex sm:flex-row-reverse sm:px-8 gap-3 bg-gray-50/80 dark:bg-dark/80 border-t border-gray-100 dark:border-white/5 backdrop-blur-sm">
                        <button type="button" @click="submitForm()" class="inline-flex w-full justify-center rounded-2xl bg-gradient-to-br from-red-500 to-red-600 px-5 py-3.5 text-sm font-black uppercase tracking-widest text-white shadow-lg shadow-red-500/20 sm:w-auto transition-all active:scale-95 hover:from-red-600 hover:to-red-700">Eliminar</button>
                        <button type="button" @click="open = false" class="mt-3 inline-flex w-full justify-center rounded-2xl bg-white dark:bg-dark dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5 border border-gray-200 px-5 py-3.5 text-sm font-black uppercase tracking-widest text-gray-900 hover:bg-gray-50 shadow-sm sm:mt-0 sm:w-auto transition-all active:scale-95">Volver</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <style>
        .pagination-custom nav {
            border-radius: 1rem;
            border: none;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const searchForm = document.getElementById('searchForm');
            let timeout = null;

            if (searchInput && searchForm) {
                // Keep focus on input and move cursor to end if it has value
                if (searchInput.value) {
                    const val = searchInput.value;
                    searchInput.focus();
                    searchInput.value = '';
                    searchInput.value = val;
                }

                searchInput.addEventListener('input', function () {
                    clearTimeout(timeout);
                    timeout = setTimeout(function () {
                        searchForm.submit();
                    }, 500); // 500ms delay
                });
            }
        });
    </script>
@endsection