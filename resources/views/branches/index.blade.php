@extends('layouts.admin')

@section('title', 'Sucursales')

@section('content')
<div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="map-pin" class="w-8 h-8 text-violet-500"></i>
                Sucursales <span class="text-violet-500 tracking-tighter">& Locales</span>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1">
                Gestioná todos tus puntos de venta desde un mismo lugar.
            </p>
        </div>
        <a href="{{ route('branch.create') }}"
            class="bg-violet-600 hover:bg-violet-700 text-white px-6 py-3 rounded-2xl shadow-xl shadow-violet-500/20 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 group w-full md:w-auto">
            <i data-lucide="plus-circle" class="w-5 h-5 group-hover:rotate-90 transition-transform"></i>
            Nueva Sucursal
        </a>
    </div>

    @if($branches->isEmpty())
        <!-- Empty State -->
        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-xl p-16 flex flex-col items-center text-center">
            <div class="w-24 h-24 bg-violet-500/10 rounded-full flex items-center justify-center mb-6 animate-pulse">
                <i data-lucide="store" class="w-12 h-12 text-violet-400"></i>
            </div>
            <h3 class="text-2xl font-black text-gray-400 italic tracking-tight">Sin sucursales registradas</h3>
            <p class="text-sm text-gray-400 mt-2 font-medium uppercase tracking-widest opacity-60">Creá tu primera sucursal o local ahora</p>
            <a href="{{ route('branch.create') }}"
                class="mt-8 bg-violet-600 text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-violet-500/20 active:scale-95 transition-all">
                Crear Primera Sucursal
            </a>
        </div>
    @else
        <!-- Branches Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($branches as $branch)
                <div class="group relative bg-white dark:bg-dark-alt rounded-[32px] border {{ $branch->is_main ? 'border-violet-500/40 shadow-2xl shadow-violet-500/10' : 'border-gray-100 dark:border-white/5' }} hover:shadow-2xl hover:shadow-violet-500/10 transition-all duration-300 overflow-hidden">
                    
                    @if($branch->is_main)
                        <div class="absolute top-4 right-4">
                            <span class="text-[9px] font-black uppercase tracking-widest bg-violet-500 text-white px-3 py-1 rounded-full shadow-lg shadow-violet-500/30">
                                Principal
                            </span>
                        </div>
                    @endif

                    @if(!$branch->is_active)
                        <div class="absolute top-4 right-4">
                            <span class="text-[9px] font-black uppercase tracking-widest bg-red-500/20 text-red-500 border border-red-500/20 px-3 py-1 rounded-full">
                                Inactiva
                            </span>
                        </div>
                    @endif

                    <div class="p-7">
                        <div class="flex items-start gap-4 mb-6">
                            <div class="w-14 h-14 bg-violet-500/10 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 flex-shrink-0">
                                <i data-lucide="store" class="w-7 h-7 text-violet-500"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-black text-gray-900 dark:text-gray-100 tracking-tight truncate">
                                    {{ $branch->name }}
                                </h3>
                                @if($branch->address)
                                    <p class="text-[11px] text-gray-400 font-bold mt-0.5 flex items-center gap-1 truncate">
                                        <i data-lucide="map-pin" class="w-3 h-3 flex-shrink-0"></i> {{ $branch->address }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Stats Row -->
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            <div class="text-center p-3 bg-indigo-500/5 rounded-2xl border border-indigo-500/10">
                                <p class="text-xl font-black text-indigo-600 tracking-tighter">{{ $branch->inventory_items_count }}</p>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Stock</p>
                            </div>
                            <div class="text-center p-3 bg-emerald-500/5 rounded-2xl border border-emerald-500/10">
                                <p class="text-xl font-black text-emerald-600 tracking-tighter">{{ $branch->sales_count }}</p>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Ventas</p>
                            </div>
                            <div class="text-center p-3 bg-orange-500/5 rounded-2xl border border-orange-500/10">
                                <p class="text-xl font-black text-orange-500 tracking-tighter">{{ $branch->repairs_count }}</p>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-0.5">Reparac.</p>
                            </div>
                        </div>

                        @if($branch->manager_name || $branch->phone)
                            <div class="space-y-1.5 mb-5">
                                @if($branch->manager_name)
                                    <p class="text-[11px] text-gray-500 font-bold flex items-center gap-2">
                                        <i data-lucide="user-check" class="w-3.5 h-3.5 text-violet-400"></i>
                                        {{ $branch->manager_name }}
                                    </p>
                                @endif
                                @if($branch->phone)
                                    <p class="text-[11px] text-gray-500 font-bold flex items-center gap-2">
                                        <i data-lucide="phone" class="w-3.5 h-3.5 text-violet-400"></i>
                                        {{ $branch->phone }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- Actions -->
                        <div class="flex items-center gap-2 pt-4 border-t border-gray-100 dark:border-white/5">
                            <a href="{{ route('branch.show', $branch) }}"
                                class="flex-1 text-center py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all active:scale-95">
                                Ver Detalle
                            </a>
                            <a href="{{ route('branch.edit', $branch) }}"
                                class="p-2.5 bg-gray-50 dark:bg-dark hover:bg-violet-100 dark:hover:bg-violet-500/10 rounded-xl transition-all border border-transparent">
                                <i data-lucide="edit-3" class="w-4 h-4 text-gray-400"></i>
                            </a>
                            @if(!$branch->is_main)
                                <form action="{{ route('branch.set-main', $branch) }}" method="POST" title="Establecer como principal">
                                    @csrf
                                    <button type="submit" class="p-2.5 bg-gray-50 dark:bg-dark hover:bg-yellow-100 dark:hover:bg-yellow-500/10 rounded-xl transition-all border border-transparent">
                                        <i data-lucide="star" class="w-4 h-4 text-gray-400"></i>
                                    </button>
                                </form>
                                <form action="{{ route('branch.destroy', $branch) }}" method="POST" onsubmit="return confirm('¿Eliminar sucursal?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2.5 bg-gray-50 dark:bg-dark hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all border border-transparent">
                                        <i data-lucide="trash-2" class="w-4 h-4 text-red-400"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
