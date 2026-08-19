@extends('layouts.admin')

@section('title', 'Detalle de Venta')

@section('content')
    <div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

        <!-- Top Header / Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ route('sale.index') }}"
                    class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 transition-all shadow-sm">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 italic">
                        Comprobante <span class="text-emerald-500 font-black">#{{ $sale->sale_number }}</span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">
                        Procesado el {{ optional($sale->sold_at)->translatedFormat('d \d\e F, Y \a \l\a\s H:i') ?? (optional($sale->created_at)->format('d/m/Y') ?? 'Fecha no registrada') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('sale.ticket', $sale) }}"
                    class="bg-dark hover:bg-dark-alt dark:bg-dark-alt dark:hover:bg-dark text-white px-6 py-3 rounded-2xl shadow-xl text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-3">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Descargar Ticket
                </a>
                <form action="{{ route('sale.destroy', $sale) }}" method="POST"
                    onsubmit="return confirm('¿Estás seguro de anular esta venta? El stock volverá al inventario.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="bg-red-500/10 text-red-600 hover:bg-red-500 hover:text-white px-6 py-3 rounded-2xl text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-3 group">
                        <i data-lucide="trash-2" class="w-4 h-4 group-hover:shake"></i>
                        Anular Venta
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">

            <!-- Items Table (Main Column) -->
            <div class="lg:col-span-2 space-y-6">
                <div
                    class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden">
                    <div class="p-8 border-b border-gray-100 dark:border-white/5">
                        <h2 class="text-xl font-black italic tracking-tight flex items-center gap-3">
                            <i data-lucide="shopping-bag" class="w-5 h-5 text-emerald-500"></i> Productos Vendidos
                        </h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr
                                    class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-widest italic">
                                    <th class="px-8 py-5 text-left uppercase">Descripción</th>
                                    <th class="px-8 py-5 text-center uppercase">Cant.</th>
                                    <th class="px-8 py-5 text-right uppercase">Unitario</th>
                                    <th class="px-8 py-5 text-right uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                                @foreach($sale->items as $item)
                                    <tr class="group transition-colors">
                                        <td class="px-8 py-6">
                                            <div class="flex flex-col">
                                                <span
                                                    class="text-sm font-black text-gray-900 dark:text-gray-100 tracking-tight">{{ $item->item_name }}</span>
                                                @if($item->inventoryItem)
                                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">IMEI: {{ $item->inventoryItem->imei ?? 'N/A' }}</span>
                                                @elseif($item->product)
                                                    <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $item->product->internal_code }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-center text-sm font-bold text-gray-600 dark:text-gray-400">
                                            {{ $item->quantity }}
                                        </td>
                                        <td
                                            class="px-8 py-6 text-right text-sm font-black text-gray-700 dark:text-gray-300 tracking-tight">
                                            {{ $sale->currency }} {{ number_format($item->unit_price, 2) }}
                                        </td>
                                        <td class="px-8 py-6 text-right text-base font-black text-emerald-600 tracking-tighter">
                                            {{ $sale->currency }} {{ number_format($item->line_total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals Section -->
                    <div class="p-8 bg-gray-50/50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
                        <div class="flex flex-col items-end space-y-3">
                            <div
                                class="flex justify-between w-full max-w-xs text-sm font-bold text-gray-400 uppercase tracking-widest italic">
                                <span>Subtotal:</span>
                                <span>{{ $sale->currency }} {{ number_format($sale->subtotal, 2) }}</span>
                            </div>
                            <div
                                class="flex justify-between w-full max-w-xs text-sm font-bold text-red-500 uppercase tracking-widest italic">
                                <span>Descuento:</span>
                                <span>- {{ $sale->currency }} {{ number_format($sale->discount, 2) }}</span>
                            </div>
                            <div
                                class="pt-4 border-t border-gray-200 dark:border-white/10 flex justify-between w-full max-w-xs">
                                <span
                                    class="text-lg font-black text-gray-900 dark:text-gray-100 uppercase italic tracking-tighter">Total:</span>
                                <span class="text-3xl font-black text-emerald-600 tracking-tighter leading-none">
                                    {{ $sale->currency }} {{ number_format($sale->total, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payments Section -->
                <div
                    class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8">
                    <h2 class="text-lg font-black italic tracking-tight flex items-center gap-3 mb-6">
                        <i data-lucide="credit-card" class="w-5 h-5 text-primary"></i> Historial de Pagos
                    </h2>
                    <div class="space-y-3">
                        @foreach($sale->payments as $payment)
                            <div
                                class="flex justify-between items-center p-4 rounded-3xl bg-gray-50/50 dark:bg-dark/40 border border-transparent hover:border-primary/20 transition-all">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 rounded-2xl bg-white dark:bg-dark-alt flex items-center justify-center border border-gray-100 dark:border-white/5 shadow-sm">
                                        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i>
                                    </div>
                                    <div>
                                        <p class="font-black text-sm text-gray-900 dark:text-gray-100 uppercase tracking-tight">
                                            {{ $payment->method }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                                            {{ optional($payment->paid_at)->format('d/m/Y H:i') ?? (optional($payment->created_at)->format('d/m/Y H:i') ?? 'N/A') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-black text-lg text-emerald-600 tracking-tighter">{{ $payment->currency }}
                                        {{ number_format($payment->amount, 2) }}</p>
                                    <span
                                        class="text-[8px] bg-emerald-500/10 text-emerald-600 px-2 py-0.5 rounded-full font-black uppercase tracking-widest border border-emerald-500/20">Pago
                                        Exitoso</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Side Column (Details) -->
            <div class="space-y-6">

                <!-- Client Card -->
                <div
                    class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 relative overflow-hidden group">
                    <div
                        class="absolute -right-4 -bottom-4 w-24 h-24 bg-primary/5 rounded-full group-hover:scale-150 transition-transform duration-700">
                    </div>
                    <h3 class="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-6 italic">Información del
                        Cliente</h3>
                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="w-16 h-16 rounded-[24px] bg-primary/10 flex items-center justify-center border border-primary/10 shadow-sm relative z-10">
                            <i data-lucide="user" class="w-8 h-8 text-primary"></i>
                        </div>
                        <div>
                            <p class="text-xl font-black text-gray-900 dark:text-gray-100 italic tracking-tight">
                                {{ $sale->client->full_name ?? 'Consumidor Final' }}</p>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-tighter">
                                {{ $sale->client->document_number ?? 'DNI no registrado' }}</p>
                        </div>
                    </div>
                    <div class="space-y-4 relative z-10">
                        <div class="flex items-center gap-3 text-sm font-bold text-gray-500 dark:text-gray-400">
                            <i data-lucide="phone" class="w-4 h-4 text-primary"></i>
                            {{ $sale->client->phone ?? 'Sin teléfono' }}
                        </div>
                        <div class="flex items-center gap-3 text-sm font-bold text-gray-500 dark:text-gray-400">
                            <i data-lucide="mail" class="w-4 h-4 text-primary"></i>
                            {{ $sale->client->email ?? 'Sin email registrado' }}
                        </div>
                    </div>
                    @if($sale->client)
                        <a href="{{ route('client.index', ['search' => $sale->client->full_name]) }}"
                            class="mt-8 w-full py-4 bg-gray-50 dark:bg-dark hover:bg-primary hover:text-white rounded-2xl text-[10px] font-black uppercase tracking-widest text-gray-400 transition-all flex items-center justify-center gap-2">
                            Ver Historial del Cliente <i data-lucide="chevron-right" class="w-3 h-3"></i>
                        </a>
                    @endif
                </div>

                <!-- Seller Info -->
                <div
                    class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8">
                    <h3 class="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-6 italic">Detalles de
                        Operación</h3>
                    <div class="space-y-6">
                        <div class="flex items-center gap-4">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($sale->seller->name ?? 'Admin') }}&background=E5E7EB&color=6B7280"
                                class="w-10 h-10 rounded-xl" />
                            <div>
                                <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest leading-none">
                                    Vendedor</p>
                                <p class="text-sm font-black text-gray-900 dark:text-gray-100">
                                    {{ $sale->seller->name ?? 'Sistema' }}</p>
                            </div>
                        </div>
                        <div
                            class="p-4 bg-gray-50/50 dark:bg-dark/40 rounded-3xl border border-gray-100 dark:border-white/5">
                            <p class="text-[10px] font-black uppercase text-gray-400 tracking-widest leading-none mb-2">
                                Notas internas</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 italic">
                                {{ $sale->notes ?: 'No se agregaron comentarios adicionales a esta transacción.' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            aside {
                display: none !important;
            }

            header {
                display: none !important;
            }

            main {
                padding: 0 !important;
                margin: 0 !important;
            }

            body {
                background: white !important;
            }
        }

        .group-hover\:shake:hover {
            animation: shake 0.3s ease-in-out infinite;
        }

        @keyframes shake {

            0%,
            100% {
                transform: rotate(0);
            }

            25% {
                transform: rotate(5deg);
            }

            75% {
                transform: rotate(-5deg);
            }
        }
    </style>
@endsection