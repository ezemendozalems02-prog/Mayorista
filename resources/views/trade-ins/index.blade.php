@extends('layouts.admin')

@section('title', 'Canjes')

@section('content')
    <div class="space-y-8 animate-in transition-all duration-500">

        <!-- Top Action Panel -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="refresh-cw" class="w-8 h-8 text-indigo-500"></i> Canjes Recibidos
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Equipos tomados como parte de pago en
                    ventas.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="relative group hidden sm:block">
                    <form action="{{ route('trade-in.index') }}" method="GET" id="trade-in-search-form">
                        <i data-lucide="search"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-indigo-500 transition-colors pointer-events-none"></i>
                        <input name="search" value="{{ request('search') }}" type="text" id="trade-in-search-input"
                            placeholder="Buscar por modelo o IMEI..."
                            class="pl-11 pr-4 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none focus:border-indigo-500 transition-all w-64 shadow-sm text-sm font-medium">
                    </form>
                </div>
            </div>
        </div>

        <!-- Table Container -->
        <div
            class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-indigo-500/5 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr
                            class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Equipo Tomado</th>
                            <th class="px-8 py-5 text-left">Estado / Batería</th>
                            <th class="px-8 py-5 text-left">Venta Asociada</th>
                            <th class="px-8 py-5 text-left">Valor Tomado</th>
                            <th class="px-8 py-5 text-right">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($tradeIns as $tradeIn)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center border border-indigo-500/5 group-hover:scale-110 transition-transform">
                                            <i data-lucide="smartphone" class="w-6 h-6 text-indigo-500"></i>
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">
                                                {{ $tradeIn->brand }} {{ $tradeIn->model }} ({{ $tradeIn->storage }})
                                            </p>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter italic">
                                                IMEI: {{ $tradeIn->imei ?? 'S/N' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1">
                                        <span
                                            class="text-[10px] font-black uppercase px-2 py-0.5 bg-gray-100 dark:bg-dark rounded-md text-gray-500 w-fit">
                                            Condición: {{ $tradeIn->condition }}
                                        </span>
                                        <span
                                            class="text-[10px] font-black uppercase px-2 py-0.5 bg-emerald-500/10 text-emerald-600 rounded-md border border-emerald-500/20 w-fit">
                                            Batería: {{ $tradeIn->battery_health }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <a href="{{ route('sale.show', $tradeIn->sale_id) }}"
                                        class="text-xs font-black text-gray-900 dark:text-gray-100 hover:text-indigo-500 underline decoration-indigo-500/20 italic">
                                        #{{ $tradeIn->sale->sale_number }}
                                    </a>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase">
                                        {{ $tradeIn->client->full_name ?? 'C. Final' }}</p>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-indigo-600 italic">
                                        {{ $tradeIn->currency }} {{ number_format($tradeIn->appraised_value, 2, ',', '.') }}
                                    </p>
                                </td>
                                <td class="px-8 py-6 text-right text-xs font-bold text-gray-400 italic">
                                    {{ optional($tradeIn->created_at)->format('d/m/Y') ?? 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="refresh-cw" class="w-12 h-12 mb-4 opacity-20"></i>
                                        <p class="text-sm font-bold uppercase italic mt-1 opacity-50">No se registraron canjes
                                            aún</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-8 bg-gray-50/30 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
                {{ $tradeIns->links() }}
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('trade-in-search-input');
            const searchForm = document.getElementById('trade-in-search-form');
            let debounceTimer = null;

            if (searchInput && searchForm) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function () {
                        searchForm.submit();
                    }, 2000);
                });
            }
        });
    </script>
@endsection