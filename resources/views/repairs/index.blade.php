@extends('layouts.admin')

@section('title', 'Reparaciones')

@section('content')
    <div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

        <!-- Action Panel -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="wrench" class="w-8 h-8 text-orange-500"></i> Servicio Técnico
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">Gestioná la entrada,
                    diagnóstico y entrega de equipos.</p>
            </div>
            <div class="flex items-center gap-3 group">
                <div class="relative w-full sm:w-auto">
                    <form action="{{ route('repair.index') }}" method="GET">
                        <i data-lucide="search"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-orange-500 transition-colors"></i>
                        <input name="search" value="{{ request('search') }}" type="text" placeholder="Ticket # o Modelo..."
                            class="pl-11 pr-4 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none focus:border-orange-500 focus:ring-4 focus:ring-orange-500/5 transition-all w-full sm:w-64 shadow-sm text-sm font-medium">
                    </form>
                </div>
                <a href="{{ route('repair.create') }}"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-8 py-3 rounded-2xl shadow-xl shadow-orange-500/20 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-2">
                    <i data-lucide="plus-square" class="w-5 h-5 group-hover:rotate-6 transition-transform"></i>
                    Nueva Orden
                </a>
            </div>
        </div>

        <!-- Status Filters -->
        <div
            class="flex flex-wrap items-center gap-2 p-1.5 bg-white dark:bg-dark-alt rounded-[28px] border border-gray-100 dark:border-white/5 shadow-sm w-fit">
            @foreach(['all' => 'Todas', 'pending' => 'Pendientes', 'in_progress' => 'En Taller', 'ready' => 'Listas', 'delivered' => 'Entregadas'] as $key => $label)
                <a href="{{ route('repair.index', ['status' => $key == 'all' ? null : $key]) }}"
                    class="px-6 py-2 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all {{ (request('status') == $key || (!request('status') && $key == 'all')) ? 'bg-orange-600 text-white shadow-lg shadow-orange-500/20' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Stats summary (compact) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-orange-500/10 p-6 rounded-[32px] border border-orange-500/10 relative overflow-hidden group">
                <p class="text-[10px] font-black uppercase text-orange-600 tracking-widest opacity-70">En Cola</p>
                <p class="text-3xl font-black text-orange-600 italic">{{ $stats['pending'] }}</p>
                <i data-lucide="clock"
                    class="absolute -right-2 -bottom-2 w-16 h-16 text-orange-500/10 group-hover:scale-110 transition-transform"></i>
            </div>
            <div class="bg-blue-500/10 p-6 rounded-[32px] border border-blue-500/10 relative overflow-hidden group">
                <p class="text-[10px] font-black uppercase text-blue-600 tracking-widest opacity-70">En Proceso</p>
                <p class="text-3xl font-black text-blue-600 italic">{{ $stats['in_progress'] }}</p>
                <i data-lucide="cpu"
                    class="absolute -right-2 -bottom-2 w-16 h-16 text-blue-500/10 group-hover:rotate-12 transition-transform"></i>
            </div>
            <div class="bg-emerald-500/10 p-6 rounded-[32px] border border-emerald-500/10 relative overflow-hidden group">
                <p class="text-[10px] font-black uppercase text-emerald-600 tracking-widest opacity-70">Listas para entregar
                </p>
                <p class="text-3xl font-black text-emerald-600 italic">{{ $stats['ready'] }}</p>
                <i data-lucide="check-circle"
                    class="absolute -right-2 -bottom-2 w-16 h-16 text-emerald-500/10 group-hover:scale-110 transition-transform"></i>
            </div>
            <div class="bg-zinc-500/10 p-6 rounded-[32px] border border-zinc-500/10 relative overflow-hidden group">
                <p class="text-[10px] font-black uppercase text-zinc-600 tracking-widest opacity-70">Entregados Hoy</p>
                <p class="text-3xl font-black text-zinc-600 italic">{{ $stats['delivered_today'] }}</p>
                <i data-lucide="package"
                    class="absolute -right-2 -bottom-2 w-16 h-16 text-zinc-500/10 group-hover:translate-x-1 transition-transform"></i>
            </div>
        </div>

        <!-- Main Table Container -->
        <div
            class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-orange-500/5 dark:shadow-none overflow-hidden relative group">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr
                            class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                             <th class="px-8 py-5 text-left">Orden / Equipo</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Cliente</th>
                            <th class="px-8 py-5 text-left hidden lg:table-cell">Falla / Diagnóstico</th>
                            <th class="px-8 py-5 text-left hidden md:table-cell">Prioridad</th>
                            <th class="px-8 py-5 text-left">Estado</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($repairs as $repair)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all group overflow-hidden">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded-2xl bg-orange-50 dark:bg-white/5 flex items-center justify-center border border-orange-100 dark:border-white/10 shadow-sm relative group-hover:scale-105 transition-transform overflow-hidden">
                                            <i data-lucide="smartphone" class="w-6 h-6 text-orange-500"></i>
                                        </div>
                                        <div>
                                            <p
                                                class="text-base font-black text-gray-900 dark:text-gray-100 group-hover:text-orange-600 transition-colors tracking-tight">
                                                {{ $repair->device_model }}
                                            </p>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                                                {{ $repair->repair_number }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden sm:table-cell">
                                    <div class="space-y-1">
                                        <p class="text-sm font-black text-gray-700 dark:text-gray-200">
                                            {{ $repair->client->full_name }}
                                        </p>
                                        <p class="text-[10px] text-gray-500 font-bold tracking-tighter uppercase"><i
                                                data-lucide="phone" class="w-3 h-3 inline mr-1"></i>
                                            {{ $repair->client->phone ?? 'Sin teléfono' }}</p>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden lg:table-cell">
                                    <div class="max-w-[180px]">
                                        <p class="text-xs text-gray-600 dark:text-gray-400 font-medium line-clamp-1 italic">
                                            {{ $repair->reported_issue }}
                                        </p>
                                        <p class="text-[10px] text-orange-500 font-bold uppercase tracking-widest mt-1">
                                            Estimado: ${{ number_format($repair->estimated_cost, 2, ',', '.') }}</p>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden md:table-cell">
                                    @php
                                        $colors = [
                                            'urgent' => 'bg-red-400 text-black border-red-500',
                                            'high' => 'bg-orange-400 text-black border-orange-500',
                                            'medium' => 'bg-blue-400 text-black border-blue-500',
                                            'low' => 'bg-zinc-400 text-black border-zinc-500'
                                        ];
                                        $color = $colors[$repair->priority->value] ?? 'bg-white text-black';
                                    @endphp
                                    <span
                                        class="text-[10px] font-black uppercase {{ $color }} px-3 py-1 rounded-full border shadow-sm">
                                        {{ $repair->priority->value }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div x-data="{ state: '{{ $repair->status->value }}' }">
                                        <span
                                            class="text-[10px] font-black uppercase bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 px-4 py-1.5 rounded-full border-none shadow-sm shadow-zinc-500/20 animate-pulse">
                                            {{ $repair->status->value }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 group-hover:translate-x-0 transition-transform">
                                        <a href="{{ route('repair.edit', $repair) }}"
                                            class="p-3 bg-gray-50 dark:bg-dark hover:bg-orange-600 hover:text-white transition-all rounded-2xl border border-transparent shadow-sm">
                                            <i data-lucide="settings-2" class="w-4 h-4"></i>
                                        </a>
                                        <button
                                            class="p-3 bg-gray-50 dark:bg-dark hover:bg-orange-600 hover:text-white transition-all rounded-2xl border border-transparent shadow-sm">
                                            <i data-lucide="printer" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-32 text-center relative overflow-hidden">
                                    <div class="flex flex-col items-center justify-center text-gray-400 relative z-10">
                                        <div
                                            class="w-20 h-20 bg-orange-500/5 rounded-[40%] flex items-center justify-center mb-6 animate-spin-slow">
                                            <i data-lucide="wrench" class="w-10 h-10 text-orange-300"></i>
                                        </div>
                                        <h3 class="text-xl font-black text-gray-400 tracking-tight italic">No hay órdenes
                                            activas</h3>
                                        <p class="text-xs font-bold mt-2 uppercase tracking-[0.3em] opacity-40">El taller está
                                            al día</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($repairs->hasPages())
                <div
                    class="px-8 py-6 bg-gray-50/50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 flex justify-end">
                    {{ $repairs->links() }}
                </div>
            @endif
        </div>

    </div>

    <style>
        @keyframes spin-slow {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin-slow {
            animation: spin-slow 8s linear infinite;
        }
    </style>
@endsection