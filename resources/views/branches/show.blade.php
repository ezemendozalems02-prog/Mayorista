@extends('layouts.admin')

@section('title', $branch->name)

@section('content')
<div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('branch.index') }}"
                class="p-2.5 bg-white dark:bg-dark-alt rounded-xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all shadow-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 text-gray-400"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black tracking-tight dark:text-gray-100 flex items-center gap-2 italic">
                    <i data-lucide="store" class="w-7 h-7 text-violet-500"></i>
                    {{ $branch->name }}
                    @if($branch->is_main)
                        <span class="text-[10px] font-black uppercase tracking-widest bg-violet-500 text-white px-3 py-1 rounded-full">Principal</span>
                    @endif
                    @if(!$branch->is_active)
                        <span class="text-[10px] font-black uppercase tracking-widest bg-red-500/20 text-red-500 border border-red-500/20 px-3 py-1 rounded-full">Inactiva</span>
                    @endif
                </h1>
                @if($branch->address)
                    <p class="text-sm text-gray-400 font-medium flex items-center gap-1 mt-1">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i> {{ $branch->address }}
                    </p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('branch.edit', $branch) }}"
                class="px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Editar
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="relative overflow-hidden bg-white dark:bg-dark-alt p-6 rounded-3xl border border-gray-100 dark:border-white/5 hover:shadow-xl hover:shadow-indigo-500/5 transition-all group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-indigo-500/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Stock Activo</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tighter">{{ $stats['stock'] }}</h3>
                    <p class="text-[10px] text-indigo-500 font-black uppercase tracking-tighter mt-1">Equipos disponibles</p>
                </div>
                <div class="p-3 bg-indigo-500/10 rounded-2xl">
                    <i data-lucide="package" class="w-6 h-6 text-indigo-500"></i>
                </div>
            </div>
        </div>
        <div class="relative overflow-hidden bg-white dark:bg-dark-alt p-6 rounded-3xl border border-gray-100 dark:border-white/5 hover:shadow-xl hover:shadow-emerald-500/5 transition-all group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-emerald-500/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Ventas del Mes</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tighter">${{ number_format($stats['sales_month'], 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-emerald-500 font-black uppercase tracking-tighter mt-1">Este mes</p>
                </div>
                <div class="p-3 bg-emerald-500/10 rounded-2xl">
                    <i data-lucide="shopping-cart" class="w-6 h-6 text-emerald-500"></i>
                </div>
            </div>
        </div>
        <div class="relative overflow-hidden bg-white dark:bg-dark-alt p-6 rounded-3xl border border-gray-100 dark:border-white/5 hover:shadow-xl hover:shadow-orange-500/5 transition-all group">
            <div class="absolute -right-4 -bottom-4 w-20 h-20 bg-orange-500/5 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Reparaciones Abiertas</p>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tighter">{{ $stats['repairs_open'] }}</h3>
                    <p class="text-[10px] text-orange-500 font-black uppercase tracking-tighter mt-1">En taller</p>
                </div>
                <div class="p-3 bg-orange-500/10 rounded-2xl">
                    <i data-lucide="wrench" class="w-6 h-6 text-orange-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-xl p-8 md:p-10">
        <h2 class="text-xs font-black text-violet-500 uppercase tracking-[0.3em] flex items-center gap-2 italic mb-6">
            <i data-lucide="info" class="w-4 h-4"></i> Datos de la Sucursal
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            @if($branch->phone)
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Teléfono</p>
                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 mt-1">{{ $branch->phone }}</p>
                </div>
            @endif
            @if($branch->email)
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Email</p>
                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 mt-1">{{ $branch->email }}</p>
                </div>
            @endif
            @if($branch->manager_name)
                <div>
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Encargado</p>
                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 mt-1">{{ $branch->manager_name }}</p>
                </div>
            @endif
            <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Estado</p>
                <p class="text-sm font-black mt-1">
                    @if($branch->is_active)
                        <span class="text-emerald-500 flex items-center gap-1"><i data-lucide="check-circle" class="w-4 h-4"></i> Activa</span>
                    @else
                        <span class="text-red-500 flex items-center gap-1"><i data-lucide="x-circle" class="w-4 h-4"></i> Inactiva</span>
                    @endif
                </p>
            </div>
            @if($branch->notes)
                <div class="sm:col-span-2">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Notas</p>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 italic">{{ $branch->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Stock -->
    @if($branch->inventoryItems->count() > 0)
    <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-xl overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-white/5 flex items-center justify-between">
            <h2 class="text-base font-black text-gray-900 dark:text-gray-100 italic flex items-center gap-2">
                <i data-lucide="package" class="w-5 h-5 text-indigo-500"></i> Stock Asignado
            </h2>
            <a href="{{ route('inventory.index') }}?branch_id={{ $branch->id }}"
                class="text-[10px] font-black uppercase tracking-widest text-primary hover:underline">Ver todo</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50 dark:bg-white/5 text-[10px] text-gray-400 font-black uppercase tracking-widest">
                    <tr>
                        <th class="px-8 py-4 text-left">Modelo</th>
                        <th class="px-8 py-4 text-left hidden sm:table-cell">Estado</th>
                        <th class="px-8 py-4 text-left">Precio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    @foreach($branch->inventoryItems->take(8) as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all">
                            <td class="px-8 py-4">
                                <p class="text-sm font-black text-gray-900 dark:text-gray-100">{{ $item->brand }} {{ $item->model }}</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">{{ $item->storage }} · {{ $item->color }}</p>
                            </td>
                            <td class="px-8 py-4 hidden sm:table-cell">
                                <span class="text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-600 px-3 py-1 rounded-full border border-emerald-500/20">
                                    {{ $item->status->value }}
                                </span>
                            </td>
                            <td class="px-8 py-4 text-sm font-black text-indigo-600">${{ number_format($item->sale_price, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
