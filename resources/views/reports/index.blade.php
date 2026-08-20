@extends('layouts.admin')

@section('title', 'Reportes y Métricas')

@section('content')
<div class="space-y-8 pb-24" id="reports-root">

    {{-- ══════════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════════ --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-5">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="bar-chart-3" class="w-8 h-8 text-violet-500"></i>
                Panel de Reportes
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1">
                Visión 360° de tu negocio en tiempo real
            </p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center">
            {{-- Live Badge --}}
            <div class="flex items-center gap-2 px-4 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                <span class="text-xs font-black text-emerald-500 uppercase tracking-widest">En Vivo</span>
                <span id="live-clock" class="text-xs font-bold text-emerald-400"></span>
            </div>
            {{-- Range Filter --}}
            <div class="flex items-center bg-white dark:bg-dark-alt p-1.5 rounded-2xl border border-gray-100 dark:border-white/5 shadow-sm">
                @foreach(['today' => 'Hoy', 'week' => 'Semana', 'month' => 'Mes', 'year' => 'Año'] as $key => $label)
                    <a href="{{ route('report.index', ['range' => $key]) }}"
                       class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all
                              {{ $range == $key ? 'bg-violet-500 text-white shadow-lg shadow-violet-500/20' : 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         LIVE REALTIME BAR (Today's snapshot)
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-violet-600 to-violet-800 p-5 rounded-3xl text-white shadow-xl shadow-violet-500/20 relative overflow-hidden">
            <p class="text-[9px] font-black uppercase tracking-widest opacity-60 mb-1">💰 Ventas Hoy</p>
            <p id="rt-today-total" class="text-2xl font-black italic tracking-tighter">USD —</p>
            <p id="rt-today-count" class="text-[10px] opacity-60 mt-1">— órdenes</p>
            <div class="absolute -right-3 -bottom-3 w-16 h-16 bg-white/10 rounded-full"></div>
        </div>
        <div class="bg-white dark:bg-dark-alt p-5 rounded-3xl border border-gray-100 dark:border-white/5 shadow-lg relative overflow-hidden">
            <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">🚚 Compras Hoy</p>
            <p id="rt-today-purchases" class="text-2xl font-black italic tracking-tighter dark:text-gray-100">—</p>
            <p class="text-[10px] text-gray-400 mt-1">recibidas</p>
        </div>
        <div class="bg-white dark:bg-dark-alt p-5 rounded-3xl border border-gray-100 dark:border-white/5 shadow-lg relative overflow-hidden">
            <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">💵 Caja</p>
            <p id="rt-cash-status" class="text-2xl font-black italic tracking-tighter dark:text-gray-100">—</p>
            <p class="text-[10px] text-gray-400 mt-1">estado del turno</p>
        </div>
        <div class="bg-white dark:bg-dark-alt p-5 rounded-3xl border border-gray-100 dark:border-white/5 shadow-lg relative overflow-hidden">
            <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">🛒 Última Venta</p>
            <p id="rt-last-sale" class="text-2xl font-black italic tracking-tighter dark:text-gray-100">—</p>
            <p id="rt-last-time" class="text-[10px] text-gray-400 mt-1">—</p>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         KPI GRID PRINCIPAL
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
        {{-- Ventas Totales --}}
        <div class="bg-white dark:bg-dark-alt p-6 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest italic mb-1">Ventas Totales</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">
                    USD {{ number_format($totalSales, 2, ',', '.') }}
                </h3>
                <div class="mt-3 flex items-center gap-1.5 text-emerald-500">
                    <i data-lucide="trending-up" class="w-3 h-3"></i>
                    <span class="text-[9px] font-black uppercase tracking-widest">{{ $totalOrders }} órdenes</span>
                </div>
            </div>
            <i data-lucide="shopping-bag" class="absolute -right-3 -bottom-3 w-20 h-20 text-violet-500/5 group-hover:scale-110 transition-transform duration-300"></i>
        </div>

        {{-- Utilidad Neta --}}
        <div class="bg-violet-600 p-6 rounded-[32px] text-white shadow-2xl shadow-violet-500/25 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[9px] font-black uppercase tracking-widest italic opacity-60 mb-1">Utilidad Neta</p>
                <h3 class="text-2xl font-black italic tracking-tighter">USD {{ number_format($totalProfit, 2, ',', '.') }}</h3>
                <div class="mt-3">
                    <div class="flex justify-between mb-1">
                        <span class="text-[9px] opacity-60 font-bold">Margen</span>
                        <span class="text-[9px] font-black">{{ $marginPercent }}%</span>
                    </div>
                    <div class="w-full bg-white/20 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-white h-full rounded-full transition-all duration-1000" style="width: {{ min($marginPercent, 100) }}%"></div>
                    </div>
                </div>
            </div>
            <i data-lucide="zap" class="absolute -right-3 -bottom-3 w-20 h-20 text-white/10 group-hover:rotate-12 transition-transform duration-300"></i>
        </div>

        {{-- Ticket Promedio --}}
        <div class="bg-white dark:bg-dark-alt p-6 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest italic mb-1">Ticket Promedio</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">
                    USD {{ number_format($avgTicket, 2, ',', '.') }}
                </h3>
                <p class="text-[9px] font-bold text-gray-400 uppercase mt-3">Por venta</p>
            </div>
            <i data-lucide="receipt" class="absolute -right-3 -bottom-3 w-20 h-20 text-amber-500/5 group-hover:scale-110 transition-transform duration-300"></i>
        </div>

        {{-- Compras a Proveedores --}}
        <div class="bg-white dark:bg-dark-alt p-6 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest italic mb-1">Compras a Proveedores</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">
                    USD {{ number_format($purchasesTotal, 2, ',', '.') }}
                </h3>
                <p class="text-[9px] font-bold text-gray-400 uppercase mt-3">{{ $purchasesCount }} recibidas</p>
            </div>
            <i data-lucide="truck" class="absolute -right-3 -bottom-3 w-20 h-20 text-blue-500/5 group-hover:-rotate-12 transition-transform duration-300"></i>
        </div>

        {{-- Clientes Nuevos --}}
        <div class="bg-white dark:bg-dark-alt p-6 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest italic mb-1">Clientes Nuevos</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">
                    +{{ $newClients }}
                </h3>
                <p class="text-[9px] font-bold text-gray-400 uppercase mt-3">Nuevos registros</p>
            </div>
            <i data-lucide="users" class="absolute -right-3 -bottom-3 w-20 h-20 text-emerald-500/5 group-hover:scale-110 transition-transform duration-300"></i>
        </div>

        {{-- Stock Disponible --}}
        <div class="bg-white dark:bg-dark-alt p-6 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest italic mb-1">Stock Disponible</p>
                <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">
                    {{ $stockAvailable }}
                </h3>
                <p class="text-[9px] font-bold text-gray-400 uppercase mt-3">artículos</p>
            </div>
            <i data-lucide="package" class="absolute -right-3 -bottom-3 w-20 h-20 text-indigo-500/5 group-hover:scale-110 transition-transform duration-300"></i>
        </div>

        <div class="col-span-2 bg-gradient-to-br from-slate-800 to-slate-900 p-6 rounded-[32px] text-white shadow-2xl shadow-slate-900/20 relative overflow-hidden group">
            <div class="relative z-10">
                <p class="text-[9px] font-black uppercase tracking-widest italic opacity-50 mb-1">Valor Venta Stock</p>
                <h3 class="text-3xl font-black italic tracking-tighter text-emerald-400">USD {{ number_format($stockValue, 2, ',', '.') }}</h3>
                
                <div class="mt-4 pt-4 border-t border-white/5">
                    <p class="text-[9px] font-black uppercase tracking-widest italic opacity-50 mb-1">Inversión Total (Costo)</p>
                    <p class="text-xl font-black italic tracking-tighter text-gray-300">USD {{ number_format($stockCost, 2, ',', '.') }}</p>
                </div>
            </div>
            <i data-lucide="layers" class="absolute -right-4 -bottom-4 w-28 h-28 text-white/5 group-hover:rotate-6 transition-transform duration-300"></i>
            <div class="absolute top-0 right-0 w-40 h-40 bg-violet-500/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         CURVA DE INGRESOS + FACTURACIÓN DIARIA
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Curva de Ingresos --}}
        <div class="lg:col-span-2 bg-white dark:bg-dark-alt p-7 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2">
                    <i data-lucide="activity" class="w-5 h-5 text-violet-500"></i> Curva de Ingresos
                </h3>
                <span class="text-[9px] font-black text-gray-400 uppercase bg-gray-100 dark:bg-dark px-3 py-1 rounded-full">
                    {{ ucfirst($range) }}
                </span>
            </div>
            <div class="h-64 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Facturación Diaria por Hora --}}
        <div class="bg-white dark:bg-dark-alt p-7 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5">
            <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2 mb-6">
                <i data-lucide="clock" class="w-5 h-5 text-amber-500"></i> Facturación Hoy
            </h3>
            <div class="h-64 w-full">
                <canvas id="hourlyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         VENTAS POR SUCURSAL + VENDEDOR
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Ventas por Sucursal --}}
        <div class="bg-white dark:bg-dark-alt p-7 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5">
            <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2 mb-6">
                <i data-lucide="map-pin" class="w-5 h-5 text-violet-500"></i> Ventas por Sucursal
            </h3>
            @if($salesByBranch->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-600">
                    <i data-lucide="building-2" class="w-10 h-10 mb-2"></i>
                    <p class="text-xs font-bold">Sin datos en este período</p>
                </div>
            @else
                @php $maxBranch = $salesByBranch->max('total') ?: 1; @endphp
                <div class="space-y-4">
                    @foreach($salesByBranch as $i => $branch)
                    @php
                        $pct = ($branch->total / $maxBranch) * 100;
                        $colors = ['bg-violet-500','bg-indigo-500','bg-blue-500','bg-cyan-500','bg-teal-500'];
                        $color = $colors[$i % count($colors)];
                    @endphp
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $color }}"></span>
                                <span class="text-xs font-black dark:text-gray-200 truncate max-w-[140px]">{{ $branch->branch_name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-black dark:text-gray-100">USD {{ number_format($branch->total, 2, ',', '.') }}</span>
                                <span class="text-[9px] text-gray-400 ml-1">({{ $branch->count }} vtas)</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-dark h-2 rounded-full overflow-hidden">
                            <div class="{{ $color }} h-full rounded-full transition-all duration-700" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-white/5 grid grid-cols-2 gap-3">
                    <div class="bg-violet-500/5 rounded-2xl p-3 text-center">
                        <p class="text-[9px] text-gray-400 font-black uppercase">Sucursales activas</p>
                        <p class="text-xl font-black text-violet-500 mt-0.5">{{ $salesByBranch->count() }}</p>
                    </div>
                    <div class="bg-violet-500/5 rounded-2xl p-3 text-center">
                        <p class="text-[9px] text-gray-400 font-black uppercase">Total general</p>
                        <p class="text-xl font-black text-gray-900 dark:text-gray-100 mt-0.5">{{ number_format($salesByBranch->sum('count'), 0, ',', '.') }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Ventas por Vendedor --}}
        <div class="bg-white dark:bg-dark-alt p-7 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5">
            <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2 mb-6">
                <i data-lucide="user-check" class="w-5 h-5 text-emerald-500"></i> Ranking de Vendedores
            </h3>
            @if($salesBySeller->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-600">
                    <i data-lucide="users" class="w-10 h-10 mb-2"></i>
                    <p class="text-xs font-bold">Sin datos en este período</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($salesBySeller as $i => $seller)
                    @php
                        $medals = ['🥇','🥈','🥉'];
                        $medal = $medals[$i] ?? '#'.($i+1);
                        $maxSeller = $salesBySeller->max('total') ?: 1;
                        $pct = ($seller->total / $maxSeller) * 100;
                    @endphp
                    <div class="flex items-center gap-3 p-3 rounded-2xl bg-gray-50 dark:bg-dark group hover:bg-violet-500/5 transition-colors">
                        <span class="text-lg w-8 text-center">{{ $medal }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black dark:text-gray-200 truncate">{{ $seller->seller_name }}</p>
                            <div class="w-full bg-gray-200 dark:bg-dark-alt h-1.5 rounded-full mt-1.5 overflow-hidden">
                                <div class="bg-emerald-400 h-full rounded-full transition-all duration-700" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs font-black dark:text-gray-100">USD {{ number_format($seller->total, 0, ',', '.') }}</p>
                            <p class="text-[9px] text-gray-400">{{ $seller->count }} vtas</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         PRODUCTOS MÁS VENDIDOS + MÉTODOS DE PAGO
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Top Productos (wider) --}}
        <div class="lg:col-span-3 bg-white dark:bg-dark-alt p-7 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5">
            <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2 mb-6">
                <i data-lucide="flame" class="w-5 h-5 text-orange-500"></i> Productos Más Vendidos
            </h3>
            @if($topProducts->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-600">
                    <i data-lucide="package-x" class="w-10 h-10 mb-2"></i>
                    <p class="text-xs font-bold">Sin datos en este período</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-[9px] font-black uppercase tracking-widest text-gray-400">
                                <th class="text-left pb-3">#</th>
                                <th class="text-left pb-3">Producto</th>
                                <th class="text-right pb-3">Qty</th>
                                <th class="text-right pb-3">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                            @foreach($topProducts as $i => $product)
                            <tr class="group hover:bg-orange-500/5 transition-colors rounded-xl">
                                <td class="py-3 pr-3">
                                    <span class="w-6 h-6 rounded-lg bg-orange-500/10 text-orange-500 text-[9px] font-black flex items-center justify-center">
                                        {{ $i + 1 }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <p class="text-xs font-black dark:text-gray-200 truncate max-w-[180px]">{{ $product->item_name }}</p>
                                    <p class="text-[9px] text-gray-400">{{ $product->sale_count }} ventas</p>
                                </td>
                                <td class="py-3 text-right">
                                    <span class="text-xs font-black text-orange-500">× {{ $product->total_qty }}</span>
                                </td>
                                <td class="py-3 text-right">
                                    <span class="text-xs font-black dark:text-gray-100">USD {{ number_format($product->total_revenue, 0, ',', '.') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Métodos de Pago --}}
        <div class="lg:col-span-2 bg-white dark:bg-dark-alt p-7 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5">
            <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2 mb-6">
                <i data-lucide="credit-card" class="w-5 h-5 text-blue-500"></i> Métodos de Pago
            </h3>
            @if($paymentMethods->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-600">
                    <i data-lucide="wallet" class="w-10 h-10 mb-2"></i>
                    <p class="text-xs font-bold">Sin datos</p>
                </div>
            @else
                <div class="h-44 w-full mb-4">
                    <canvas id="paymentChart"></canvas>
                </div>
                <div class="space-y-2">
                    @php
                        $pmColors = ['#8b5cf6','#3b82f6','#10b981','#f59e0b','#ef4444'];
                    @endphp
                    @foreach($paymentMethods as $i => $pm)
                    @php
                        $pct = round(($pm->total / $totalPayments) * 100, 1);
                        $color = $pmColors[$i % count($pmColors)];
                    @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $color }}"></span>
                            <span class="text-[10px] font-black dark:text-gray-300 uppercase">{{ $pm->method }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[9px] text-gray-400">{{ $pct }}%</span>
                            <span class="text-[10px] font-black dark:text-gray-100">USD {{ number_format($pm->total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         COMPRAS POR PROVEEDOR + CUENTAS POR COBRAR
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Compras por Proveedor --}}
        <div class="bg-white dark:bg-dark-alt p-7 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5">
            <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2 mb-6">
                <i data-lucide="truck" class="w-5 h-5 text-blue-500"></i> Compras por Proveedor
            </h3>
            @if($purchasesBySupplier->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-600">
                    <i data-lucide="package-x" class="w-10 h-10 mb-2"></i>
                    <p class="text-xs font-bold">Sin compras recibidas en este período</p>
                </div>
            @else
                @php $maxSupplier = $purchasesBySupplier->max('total') ?: 1; @endphp
                <div class="space-y-4">
                    @foreach($purchasesBySupplier as $supplier)
                    @php $pct = ($supplier->total / $maxSupplier) * 100; @endphp
                    <div class="p-4 rounded-2xl bg-gray-50 dark:bg-dark">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-xl bg-blue-500/10 flex items-center justify-center shrink-0">
                                    <i data-lucide="truck" class="w-4 h-4 text-blue-500"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-black dark:text-gray-200">{{ $supplier->supplier_name }}</p>
                                    <p class="text-[9px] text-gray-400">{{ $supplier->count }} compras</p>
                                </div>
                            </div>
                            <p class="text-xs font-black dark:text-gray-100">USD {{ number_format($supplier->total, 0, ',', '.') }}</p>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-dark-alt h-1.5 rounded-full overflow-hidden">
                            <div class="bg-blue-400 h-full rounded-full transition-all duration-700" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Cuentas por Cobrar --}}
        <div class="bg-white dark:bg-dark-alt p-7 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5">
            <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2 mb-2">
                <i data-lucide="wallet" class="w-5 h-5 text-red-500"></i> Cuentas por Cobrar
            </h3>
            <p class="text-2xl font-black italic tracking-tighter text-red-500 mb-5">USD {{ number_format($totalReceivable, 2, ',', '.') }}</p>
            @if($topDebtors->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-600">
                    <i data-lucide="check-circle" class="w-10 h-10 mb-2"></i>
                    <p class="text-xs font-bold">Ningún cliente tiene saldo deudor</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($topDebtors as $debtor)
                    <div class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-dark">
                        <span class="text-xs font-black dark:text-gray-200 truncate max-w-[160px]">{{ $debtor->client_name }}</span>
                        <span class="text-xs font-black text-red-500">USD {{ number_format($debtor->balance, 2, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         TOP CLIENTES + VENTAS RECIENTES
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Top Clientes --}}
        <div class="lg:col-span-2 bg-white dark:bg-dark-alt p-7 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5">
            <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2 mb-6">
                <i data-lucide="crown" class="w-5 h-5 text-amber-500"></i> Top Clientes
            </h3>
            @if($topClients->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-600">
                    <i data-lucide="users" class="w-10 h-10 mb-2"></i>
                    <p class="text-xs font-bold">Sin datos</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($topClients as $i => $client)
                    @php
                        $initials = collect(explode(' ', $client->client_name))->map(fn($w) => strtoupper(substr($w,0,1)))->take(2)->join('');
                        $bgColors = ['bg-violet-500','bg-indigo-500','bg-blue-500','bg-emerald-500','bg-amber-500'];
                        $bg = $bgColors[$i % count($bgColors)];
                    @endphp
                    <div class="flex items-center gap-3 p-3 rounded-2xl hover:bg-amber-500/5 transition-colors">
                        <div class="w-9 h-9 rounded-2xl {{ $bg }} flex items-center justify-center text-white text-[10px] font-black shrink-0">
                            {{ $initials ?: '?' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-black dark:text-gray-200 truncate">{{ $client->client_name }}</p>
                            <p class="text-[9px] text-gray-400">{{ $client->count }} compras</p>
                        </div>
                        <span class="text-xs font-black dark:text-gray-100 shrink-0">USD {{ number_format($client->total, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Ventas Recientes --}}
        <div class="lg:col-span-3 bg-white dark:bg-dark-alt p-7 rounded-[32px] border border-gray-100 dark:border-white/5 shadow-xl shadow-violet-500/5">
            <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2 mb-6">
                <i data-lucide="list" class="w-5 h-5 text-violet-500"></i> Ventas Recientes
            </h3>
            @if($recentSales->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-gray-300 dark:text-gray-600">
                    <i data-lucide="shopping-cart" class="w-10 h-10 mb-2"></i>
                    <p class="text-xs font-bold">Sin ventas recientes</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-[9px] font-black uppercase tracking-widest text-gray-400 border-b border-gray-100 dark:border-white/5">
                                <th class="text-left pb-3">Nro.</th>
                                <th class="text-left pb-3">Cliente</th>
                                <th class="text-left pb-3 hidden sm:table-cell">Sucursal</th>
                                <th class="text-left pb-3 hidden md:table-cell">Vendedor</th>
                                <th class="text-right pb-3">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                            @foreach($recentSales as $sale)
                            <tr class="hover:bg-violet-500/5 transition-colors">
                                <td class="py-2.5 pr-3">
                                    <span class="text-[10px] font-black text-violet-500">#{{ $sale->sale_number }}</span>
                                    <p class="text-[9px] text-gray-400">{{ \Carbon\Carbon::parse($sale->sold_at)->format('d/m H:i') }}</p>
                                </td>
                                <td class="py-2.5 pr-3">
                                    <span class="text-[10px] font-black dark:text-gray-200 truncate block max-w-[100px]">{{ $sale->client_name }}</span>
                                </td>
                                <td class="py-2.5 pr-3 hidden sm:table-cell">
                                    <span class="text-[9px] bg-gray-100 dark:bg-dark text-gray-500 dark:text-gray-400 px-2 py-1 rounded-lg font-bold">{{ $sale->branch_name }}</span>
                                </td>
                                <td class="py-2.5 pr-3 hidden md:table-cell">
                                    <span class="text-[9px] text-gray-500 dark:text-gray-400 font-bold">{{ $sale->seller_name }}</span>
                                </td>
                                <td class="py-2.5 text-right">
                                    <span class="text-[10px] font-black text-gray-900 dark:text-gray-100">USD {{ number_format($sale->total, 2, ',', '.') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════
     SCRIPTS: Chart.js + Realtime Polling
═══════════════════════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Helpers ───────────────────────────────────────────────────
    const isDark = () => document.documentElement.classList.contains('dark');
    const gridColor = () => isDark() ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';
    const tickColor = () => isDark() ? '#6b7280' : '#9ca3af';

    // ── 1. Curva de Ingresos ──────────────────────────────────────
    const salesData = @json($salesByDay);
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const gradient = salesCtx.createLinearGradient(0, 0, 0, 200);
    gradient.addColorStop(0, 'rgba(139,92,246,0.25)');
    gradient.addColorStop(1, 'rgba(139,92,246,0.0)');

    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: salesData.map(i => i.date),
            datasets: [{
                label: 'Ventas (USD)',
                data: salesData.map(i => parseFloat(i.total)),
                borderColor: '#8b5cf6',
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#8b5cf6',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: {
                backgroundColor: '#1f2937',
                titleColor: '#e5e7eb',
                bodyColor: '#9ca3af',
                padding: 12,
                cornerRadius: 12,
                callbacks: { label: ctx => ' USD ' + ctx.parsed.y.toFixed(2) }
            }},
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor() }, ticks: { color: tickColor(), font: { weight: '700' }, callback: v => 'USD ' + v.toLocaleString() }},
                x: { grid: { display: false }, ticks: { color: tickColor(), font: { weight: '700' }, maxRotation: 0 }}
            }
        }
    });

    // ── 2. Facturación por Hora ───────────────────────────────────
    const hourlyData = @json($todayBilling);
    const hours = Array.from({length: 24}, (_, i) => `${String(i).padStart(2,'0')}h`);
    const hourlyTotals = hours.map((_, i) => {
        const found = hourlyData.find(h => Number(h.hour) === i);
        return found ? parseFloat(found.total) : 0;
    });
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: hours,
            datasets: [{
                label: 'USD',
                data: hourlyTotals,
                backgroundColor: hourlyTotals.map(v => v > 0 ? '#f59e0b' : 'rgba(245,158,11,0.1)'),
                borderRadius: 8,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: {
                backgroundColor: '#1f2937',
                callbacks: { label: ctx => ' USD ' + ctx.parsed.y.toFixed(2) }
            }},
            scales: {
                y: { beginAtZero: true, grid: { color: gridColor() }, ticks: { color: tickColor(), font: { size: 9, weight: '700' }}},
                x: { grid: { display: false }, ticks: { color: tickColor(), font: { size: 8 }, maxTicksLimit: 12 }}
            }
        }
    });

    // ── 3. Métodos de Pago (Doughnut) ────────────────────────────
    const pmCanvas = document.getElementById('paymentChart');
    if (pmCanvas) {
        const pmData = @json($paymentMethods);
        if (pmData.length > 0) {
            new Chart(pmCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: pmData.map(p => p.method),
                    datasets: [{
                        data: pmData.map(p => parseFloat(p.total)),
                        backgroundColor: ['#8b5cf6','#3b82f6','#10b981','#f59e0b','#ef4444'],
                        borderWidth: 0,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: { legend: { display: false }, tooltip: {
                        backgroundColor: '#1f2937',
                        callbacks: { label: ctx => ' USD ' + ctx.parsed.toFixed(2) }
                    }}
                }
            });
        }
    }

    // ── 5. Reloj Live ─────────────────────────────────────────────
    function updateClock() {
        const el = document.getElementById('live-clock');
        if (el) el.textContent = new Date().toLocaleTimeString('es-AR', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ── 6. Realtime Polling ───────────────────────────────────────
    async function fetchRealtime() {
        try {
            const res = await fetch('{{ route("report.realtime") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            });
            if (!res.ok) return;
            const d = await res.json();

            const el = id => document.getElementById(id);
            if (el('rt-today-total'))     el('rt-today-total').textContent     = 'USD ' + parseFloat(d.today_total).toLocaleString('es-AR', {minimumFractionDigits:2});
            if (el('rt-today-count'))     el('rt-today-count').textContent     = d.today_count + ' órdenes';
            if (el('rt-today-purchases')) el('rt-today-purchases').textContent = d.today_purchases;
            if (el('rt-cash-status')) {
                el('rt-cash-status').textContent = d.cash_session_open
                    ? 'USD ' + parseFloat(d.cash_balance).toLocaleString('es-AR', {minimumFractionDigits:2})
                    : 'Cerrada';
            }
            if (el('rt-last-sale') && d.last_sale) {
                el('rt-last-sale').textContent = 'USD ' + parseFloat(d.last_sale.total).toLocaleString('es-AR', {minimumFractionDigits:2});
                el('rt-last-time').textContent = d.last_sale.client_name;
            }
        } catch (e) { /* silencioso */ }
    }

    fetchRealtime();
    setInterval(fetchRealtime, 30000); // cada 30 segundos
});
</script>
@endsection