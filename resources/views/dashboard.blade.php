@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6 md:space-y-8 page-transition">

        <!-- Welcome Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight dark:text-gray-100 flex items-center gap-2">
                    <span class="text-violet-gradient tracking-tighter">¡Hola,</span> {{ explode(' ', Auth::user()->name)[0] }}! 👋
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Aquí tenés el resumen de tu negocio para
                    hoy, {{ now()->format('j') }} de {{ ucfirst(now()->locale('es')->monthName) }}.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                <a href="{{ route('sale.create') }}"
                    class="btn-violet px-5 py-2.5 rounded-2xl shadow-lg text-sm font-bold flex items-center justify-center gap-2 transition-all active:scale-95 group">
                    <i data-lucide="plus-circle" class="w-4 h-4 group-hover:rotate-90 transition-transform"></i>
                    Registrar Venta
                </a>
                <a href="{{ route('order.create') }}"
                    class="bg-dark hover:bg-dark-alt dark:bg-dark-alt dark:hover:bg-dark text-white px-5 py-2.5 rounded-2xl shadow-xl text-sm font-bold flex items-center justify-center gap-2 transition-all active:scale-95 group">
                    <i data-lucide="clipboard-list" class="w-4 h-4 group-hover:-rotate-6 transition-transform"></i>
                    Nuevo Pedido
                </a>
            </div>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">

            <!-- Total Sales -->
            <div
                class="relative overflow-hidden bg-white dark:bg-dark-alt p-6 rounded-3xl border border-gray-100 dark:border-white/5 transition-all hover:shadow-2xl hover:shadow-primary/5 group">
                <div
                    class="absolute -right-4 -bottom-4 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-150 transition-transform duration-700">
                </div>
                <div class="flex items-start justify-between relative">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Ventas del Mes</p>
                        <h3 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tighter">
                            ${{ number_format($metrics['sales_month'], 2) }}</h3>
                        @if($metrics['sales_growth_percent'] !== null)
                            <p class="text-[10px] mt-2 flex items-center {{ $metrics['sales_growth_percent'] >= 0 ? 'text-emerald-500' : 'text-red-500' }} font-black tracking-tight uppercase">
                                <i data-lucide="{{ $metrics['sales_growth_percent'] >= 0 ? 'trending-up' : 'trending-down' }}" class="w-3 h-3 mr-1"></i>
                                {{ $metrics['sales_growth_percent'] >= 0 ? '+' : '' }}{{ $metrics['sales_growth_percent'] }}% vs mes ant.
                            </p>
                        @else
                            <p class="text-[10px] mt-2 text-gray-400 font-bold uppercase tracking-tight">Sin mes anterior para comparar</p>
                        @endif
                    </div>
                    <div class="p-3 bg-primary/10 rounded-2xl">
                        <i data-lucide="wallet" class="w-6 h-6 text-primary"></i>
                    </div>
                </div>
            </div>

            <!-- Profit -->
            <div
                class="relative overflow-hidden bg-white dark:bg-dark-alt p-6 rounded-3xl border border-gray-100 dark:border-white/5 transition-all hover:shadow-2xl hover:shadow-blue-500/5 group">
                <div
                    class="absolute -right-4 -bottom-4 w-24 h-24 bg-blue-500/5 rounded-full group-hover:scale-150 transition-transform duration-700">
                </div>
                <div class="flex items-start justify-between relative">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Ganancia Neta</p>
                        <h3 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tighter">
                            ${{ number_format($metrics['profit_month'], 2) }}</h3>
                        <p
                            class="text-[10px] mt-2 flex items-center text-blue-500 font-black tracking-tight uppercase italic border border-blue-100 dark:border-blue-900/30 px-2 py-0.5 rounded-full w-fit">
                            Margen: {{ $metrics['margin_percent'] }}%
                        </p>
                    </div>
                    <div class="p-3 bg-blue-500/10 rounded-2xl">
                        <i data-lucide="bar-chart-3" class="w-6 h-6 text-blue-500"></i>
                    </div>
                </div>
            </div>

            <!-- Pending Orders -->
            <div
                class="relative overflow-hidden bg-white dark:bg-dark-alt p-6 rounded-3xl border border-gray-100 dark:border-white/5 transition-all hover:shadow-2xl hover:shadow-orange-500/5 group">
                <div
                    class="absolute -right-4 -bottom-4 w-24 h-24 bg-orange-500/5 rounded-full group-hover:scale-150 transition-transform duration-700">
                </div>
                <a href="{{ route('order.index', ['status' => 'confirmed']) }}" class="flex items-start justify-between relative">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Pedidos Pendientes</p>
                        <h3 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tighter">
                            {{ $metrics['pending_orders'] }} {{ \Illuminate\Support\Str::plural('pedido', $metrics['pending_orders']) }}</h3>
                        <div class="flex items-center space-x-1 mt-2">
                            <span
                                class="w-2 h-2 bg-orange-500 rounded-full animate-pulse shadow-sm shadow-orange-500/50"></span>
                            <span class="text-[10px] text-orange-500 font-black uppercase tracking-tight">Por confirmar o facturar</span>
                        </div>
                    </div>
                    <div class="p-3 bg-orange-500/10 rounded-2xl">
                        <i data-lucide="clipboard-list" class="w-6 h-6 text-orange-500"></i>
                    </div>
                </a>
            </div>

            <!-- Stock -->
            <div
                class="relative overflow-hidden bg-white dark:bg-dark-alt p-6 rounded-3xl border border-gray-100 dark:border-white/5 transition-all hover:shadow-2xl hover:shadow-indigo-500/5 group">
                <div
                    class="absolute -right-4 -bottom-4 w-24 h-24 bg-indigo-500/5 rounded-full group-hover:scale-150 transition-transform duration-700">
                </div>
                <div class="flex items-start justify-between relative">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Stock Disponible</p>
                        <h3 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-gray-100 tracking-tighter">
                            {{ $metrics['stock_count'] }} Unidades</h3>
                        <p class="text-[10px] mt-2 flex items-center text-gray-400 font-bold uppercase tracking-tight">Valor
                            total: ${{ number_format($metrics['stock_value'], 2) }}</p>
                    </div>
                    <div class="p-3 bg-indigo-500/10 rounded-2xl">
                        <i data-lucide="package" class="w-6 h-6 text-indigo-500"></i>
                    </div>
                </div>
            </div>

        </div>

        <!-- Lists Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">

            <!-- Recent Sales -->
            <div
                class="bg-white dark:bg-dark-alt rounded-[40px] p-6 md:p-8 border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none relative group">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2
                            class="text-xl font-black text-gray-900 dark:text-gray-100 tracking-tight flex items-center gap-3">
                            <i data-lucide="credit-card" class="w-5 h-5 text-emerald-500"></i> Últimas Ventas
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Transacciones procesadas recientemente</p>
                    </div>
                    <a href="{{ route('sale.index') }}"
                        class="p-2 bg-gray-50 dark:bg-dark hover:bg-emerald-500 hover:text-white transition-all rounded-2xl group border border-gray-100 dark:border-white/5">
                        <i data-lucide="arrow-right-circle" class="w-5 h-5"></i>
                    </a>
                </div>

                <div class="space-y-3 relative">
                    @forelse($recent_sales as $sale)
                        <div
                            class="flex justify-between items-center p-4 rounded-3xl bg-gray-50/50 dark:bg-dark/40 hover:bg-white dark:hover:bg-dark hover:shadow-xl hover:shadow-emerald-500/5 border border-transparent hover:border-emerald-500/10 transition-all group overflow-hidden">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-white dark:bg-dark-alt flex items-center justify-center border border-gray-100 dark:border-white/5 shadow-sm group-hover:scale-110 transition-transform">
                                    <i data-lucide="shopping-bag" class="w-5 h-5 text-emerald-500"></i>
                                </div>
                                <div>
                                    <p class="font-black text-sm text-gray-900 dark:text-gray-100">{{ $sale->sale_number }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">
                                        {{ $sale->client->full_name ?? 'Consumidor Final' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-black text-lg text-emerald-600 tracking-tighter">
                                    ${{ number_format($sale->total, 2) }}</p>
                                <p class="text-[10px] uppercase text-gray-400 font-bold">
                                    {{ $sale->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center text-gray-400 text-center opacity-50">
                            <i data-lucide="database-zap" class="w-10 h-10 mb-2"></i>
                            <p class="text-sm">No hay ventas recientes</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Orders -->
            <div
                class="bg-white dark:bg-dark-alt rounded-[40px] p-6 md:p-8 border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none relative group">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2
                            class="text-xl font-black text-gray-900 dark:text-gray-100 tracking-tight flex items-center gap-3">
                            <i data-lucide="clipboard-list" class="w-5 h-5 text-orange-500"></i> Pedidos
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">Últimos pedidos cargados</p>
                    </div>
                    <a href="{{ route('order.index') }}"
                        class="p-2 bg-gray-50 dark:bg-dark hover:bg-orange-500 hover:text-white transition-all rounded-2xl group border border-gray-100 dark:border-white/5">
                        <i data-lucide="arrow-right-circle" class="w-5 h-5"></i>
                    </a>
                </div>

                <div class="space-y-3">
                    @php
                        $orderStatusColors = ['draft' => 'bg-gray-400/10 text-gray-500', 'confirmed' => 'bg-amber-500/10 text-amber-600', 'fulfilled' => 'bg-emerald-500/10 text-emerald-600', 'cancelled' => 'bg-red-400/10 text-red-500'];
                        $orderStatusLabels = ['draft' => 'Borrador', 'confirmed' => 'Confirmado', 'fulfilled' => 'Facturado', 'cancelled' => 'Cancelado'];
                    @endphp
                    @forelse($recent_orders as $order)
                        @php $orderStatusValue = $order->status?->value ?? 'draft'; @endphp
                        <a href="{{ route('order.show', $order) }}"
                            class="flex justify-between items-center p-4 rounded-3xl bg-gray-50/50 dark:bg-dark/40 hover:bg-white dark:hover:bg-dark hover:shadow-xl hover:shadow-orange-500/5 border border-transparent hover:border-orange-500/10 transition-all group overflow-hidden">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 rounded-2xl bg-white dark:bg-dark-alt flex items-center justify-center border border-gray-100 dark:border-white/5 shadow-sm group-hover:scale-110 transition-transform">
                                    <i data-lucide="clipboard-list" class="w-5 h-5 text-orange-500"></i>
                                </div>
                                <div>
                                    <p class="font-black text-sm text-gray-900 dark:text-gray-100">{{ $order->code }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight line-clamp-1">
                                        {{ $order->client->display_name ?? 'S/N' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-[10px] px-3 py-1 rounded-full font-black uppercase tracking-widest shadow-sm {{ $orderStatusColors[$orderStatusValue] ?? '' }}">{{ $orderStatusLabels[$orderStatusValue] ?? $orderStatusValue }}</span>
                                <p class="text-[10px] mt-2 uppercase text-gray-400 font-bold">${{ number_format($order->total, 2) }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center text-gray-400 text-center opacity-50">
                            <i data-lucide="clipboard-x" class="w-10 h-10 mb-2"></i>
                            <p class="text-sm">No hay pedidos cargados</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
@endsection