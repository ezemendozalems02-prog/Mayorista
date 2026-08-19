@extends('layouts.admin')

@section('title', 'Gestión de Negocios')

@section('content')
<div class="space-y-8 pb-20">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="building-2" class="w-8 h-8 text-violet-500"></i> Negocios Registrados
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">
                Listado y control de todos los inquilinos (tenants) del sistema.
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-dark-alt p-4 rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm">
        <form action="{{ route('super-admin.organizations.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscá por nombre o CUIT/DNI..." 
                       class="w-full pl-11 pr-4 py-3 rounded-2xl bg-gray-50 dark:bg-dark border-none outline-none focus:ring-2 ring-violet-500/20 text-sm font-medium">
            </div>
            <button type="submit" class="px-8 py-3 bg-dark dark:bg-white dark:text-dark text-white rounded-2xl text-xs font-black uppercase tracking-widest hover:scale-105 transition-all">Filtrar</button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/5 dark:shadow-none overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-widest italic">
                        <th class="px-8 py-5 text-left uppercase">Negocio</th>
                        <th class="px-8 py-5 text-center uppercase">Plan</th>
                        <th class="px-8 py-5 text-center uppercase">Usuarios</th>
                        <th class="px-8 py-5 text-center uppercase">Facturación</th>
                        <th class="px-8 py-5 text-center uppercase">Estado</th>
                        <th class="px-8 py-5 text-right uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    @foreach($organizations as $org)
                    <tr class="group hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-violet-500/10 flex items-center justify-center font-black text-violet-600 text-[10px]">
                                    {{ substr($org->name, 0, 2) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $org->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $org->document_number ?: 'Sin ID' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="text-[8px] px-3 py-1 bg-violet-500/10 text-violet-600 rounded-full font-black uppercase tracking-[0.2em] border border-violet-500/20">
                                {{ $org->plan }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">{{ $org->users_count }}</p>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <p class="text-sm font-black text-emerald-600 italic tracking-tighter">USD {{ number_format($org->sales_sum_total ?? 0, 0) }}</p>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="text-[9px] px-3 py-1 rounded-full font-black uppercase tracking-widest {{ $org->is_active ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-600' }}">
                                {{ $org->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right">
                             <a href="{{ route('super-admin.organizations.show', $org) }}" class="p-3 bg-gray-50 dark:bg-dark hover:bg-violet-500 hover:text-white transition-all rounded-2xl border border-transparent shadow-sm inline-flex">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-8 py-4 bg-gray-50/50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
            {{ $organizations->links() }}
        </div>
    </div>

</div>
@endsection
