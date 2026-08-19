@extends('layouts.admin')

@section('title', 'Ventas')

@section('content')
    <div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

        <!-- Top Action Panel -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="shopping-cart" class="w-8 h-8 text-emerald-500"></i> Facturación
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Listado histórico de ventas, comprobantes y
                    ganancias.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('sale.create') }}"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-2xl shadow-xl shadow-emerald-500/20 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-3 group">
                    <i data-lucide="plus-circle" class="w-5 h-5 group-hover:rotate-90 transition-transform"></i>
                    Nueva Venta
                </a>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white dark:bg-dark-alt rounded-[32px] border border-gray-100 dark:border-white/5 p-2 flex flex-col md:flex-row items-center gap-4 shadow-sm">
            <form action="{{ route('sale.index') }}" method="GET" class="flex-1 flex flex-col md:flex-row items-center gap-2 w-full">
                <div class="relative w-full md:w-auto flex-1">
                    <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input name="search" value="{{ request('search') }}" type="text" placeholder="Buscá por factura, cliente o teléfono..."
                        class="w-full pl-11 pr-4 py-3 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-emerald-500 outline-none transition-all text-sm font-medium">
                </div>
                
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select name="date_range" onchange="this.form.submit()" class="pl-4 pr-10 py-3 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-emerald-500 outline-none text-sm font-bold appearance-none cursor-pointer w-full md:w-44">
                        <option value="">Cualquier fecha</option>
                        <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Hoy</option>
                        <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>Esta Semana</option>
                        <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>Este Mes</option>
                    </select>
                    
                    @if(request()->anyFilled(['search', 'date_range']))
                        <a href="{{ route('sale.index') }}" class="p-3 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-colors" title="Limpiar Filtros">
                            <i data-lucide="filter-x" class="w-5 h-5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Main Table Container -->
        <div
            class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-emerald-500/5 dark:shadow-none overflow-hidden relative group">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr
                            class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Comprobante</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Cliente</th>
                            <th class="px-8 py-5 text-left hidden lg:table-cell">Modelos Vendidos</th>
                            <th class="px-8 py-5 text-left">Total</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($sales as $sale)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all group overflow-hidden">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-white/5 flex items-center justify-center border border-emerald-100 dark:border-white/10 shadow-sm relative group-hover:scale-105 transition-transform overflow-hidden">
                                            <i data-lucide="receipt" class="w-6 h-6 text-emerald-500"></i>
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-black text-gray-900 dark:text-gray-100 group-hover:text-emerald-600 transition-colors tracking-tight">
                                                #{{ $sale->sale_number }}</p>
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">
                                                {{ optional($sale->sold_at)->format('d/m/Y H:i') ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden sm:table-cell">
                                    <div class="space-y-1">
                                        <p class="text-sm font-black text-gray-700 dark:text-gray-200">
                                            {{ $sale->client->full_name ?? 'Consumidor Final' }}</p>
                                        <p
                                            class="text-[10px] text-gray-500 font-bold tracking-tighter uppercase line-clamp-1 italic">
                                            {{ $sale->client->phone ?? '-' }}</p>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden lg:table-cell">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @php $itemCount = $sale->items->count(); @endphp
                                        @foreach($sale->items->take(2) as $item)
                                            <span class="text-[10px] font-black uppercase tracking-tighter bg-gray-100 dark:bg-white/5 px-2 py-1 rounded-lg text-gray-500 dark:text-gray-400">
                                                {{ $item->inventoryItem->model ?? $item->item_name }}
                                            </span>
                                        @endforeach
                                        @if($itemCount > 2)
                                            <span class="text-[10px] font-black uppercase text-emerald-500">+{{ $itemCount - 2 }} más</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="text-left">
                                        <p class="text-lg font-black text-emerald-600 tracking-tighter">
                                            {{ $sale->currency }} {{ number_format($sale->total, 2) }}
                                        </p>
                                        <div class="flex items-center gap-1">
                                            <span class="text-[8px] font-black uppercase text-gray-400 tracking-widest">{{ $sale->seller->name ?? 'Sistema' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div
                                        class="flex items-center justify-end gap-2 group-hover:translate-x-0 transition-transform">
                                        <a href="{{ route('sale.show', $sale) }}"
                                            class="p-3 bg-gray-50 dark:bg-dark hover:bg-emerald-600 hover:text-white transition-all rounded-2xl border border-transparent shadow-sm group/btn">
                                            <i data-lucide="eye"
                                                class="w-4 h-4 transition-transform group-hover/btn:scale-110"></i>
                                        </a>
                                        <a href="{{ route('sale.ticket', $sale) }}"
                                            class="p-3 bg-gray-50 dark:bg-dark hover:bg-emerald-600 hover:text-white transition-all rounded-2xl border border-transparent shadow-sm group/btn"
                                            title="Descargar Ticket">
                                            <i data-lucide="download"
                                                class="w-4 h-4 transition-transform group-hover/btn:scale-110"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-32 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <div
                                            class="w-20 h-20 bg-emerald-500/5 rounded-full flex items-center justify-center mb-6">
                                            <i data-lucide="shopping-bag" class="w-10 h-10 text-emerald-300"></i>
                                        </div>
                                        <h3 class="text-xl font-black text-gray-400 tracking-tight italic">No se registraron
                                            ventas aún</h3>
                                        <p class="text-xs font-bold mt-2 uppercase tracking-[0.3em] opacity-40 italic">Empezá a
                                            vender hoy mismo</p>
                                        <a href="{{ route('sale.create') }}"
                                            class="mt-8 bg-emerald-600 text-white px-8 py-3 rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-emerald-500/20 active:scale-95 transition-all">Nueva
                                            Operación</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($sales->hasPages())
                <div
                    class="px-8 py-6 bg-gray-50/50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 flex justify-end">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection