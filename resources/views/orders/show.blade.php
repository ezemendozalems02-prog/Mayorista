@extends('layouts.admin')

@section('title', 'Pedido ' . $order->code)

@section('content')
    @php
        $statusColors = ['draft' => 'bg-gray-400/10 text-gray-500', 'confirmed' => 'bg-amber-500/10 text-amber-600', 'fulfilled' => 'bg-emerald-500/10 text-emerald-600', 'cancelled' => 'bg-red-400/10 text-red-500'];
        $statusLabels = ['draft' => 'Borrador', 'confirmed' => 'Confirmado', 'fulfilled' => 'Facturado', 'cancelled' => 'Cancelado'];
        $statusValue = $order->status?->value ?? 'draft';
        $isDraft = $statusValue === 'draft';
        $isConfirmed = $statusValue === 'confirmed';
        $canFulfill = $isDraft || $isConfirmed;
    @endphp

    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500" x-data="{ showFulfillModal: false }">

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="fixed bottom-8 right-8 z-[100] bg-emerald-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="fixed bottom-8 right-8 z-[100] bg-red-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic max-w-md">
                <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="clipboard-list" class="w-8 h-8 text-primary"></i> {{ $order->code }}
                    <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full {{ $statusColors[$statusValue] ?? '' }}">
                        {{ $statusLabels[$statusValue] ?? $statusValue }}
                    </span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    {{ $order->client->display_name ?? '—' }} · Cargado por {{ $order->creator->name ?? '—' }} el {{ $order->created_at->format('d/m/Y H:i') }}
                    @if($order->fulfilled_at)
                        · Facturado el {{ $order->fulfilled_at->format('d/m/Y H:i') }}
                    @endif
                </p>
            </div>
            <a href="{{ route('order.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        @if($order->sale)
            <a href="{{ route('sale.show', $order->sale) }}"
                class="flex items-center justify-between bg-emerald-500/10 border border-emerald-500/20 rounded-2xl px-6 py-4 hover:bg-emerald-500/15 transition-all group">
                <span class="text-xs font-black text-emerald-600 uppercase tracking-widest flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4"></i> Facturado como venta {{ $order->sale->sale_number }}
                </span>
                <i data-lucide="arrow-right" class="w-4 h-4 text-emerald-600 group-hover:translate-x-1 transition-transform"></i>
            </a>
        @endif

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Producto</th>
                            <th class="px-8 py-5 text-center">Cant.</th>
                            <th class="px-8 py-5 text-right">Precio Unit.</th>
                            <th class="px-8 py-5 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @foreach($order->items as $item)
                            <tr>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $item->item_name }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter italic">{{ $item->product->internal_code ?? '—' }}</p>
                                </td>
                                <td class="px-8 py-6 text-center text-sm font-bold text-gray-600 dark:text-gray-400">{{ $item->quantity }}</td>
                                <td class="px-8 py-6 text-right text-sm font-bold text-gray-600 dark:text-gray-400">${{ number_format($item->unit_price, 2, ',', '.') }}</td>
                                <td class="px-8 py-6 text-right text-sm font-black text-primary italic">${{ number_format($item->line_total, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-8 bg-gray-50/30 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 space-y-2">
                <div class="flex justify-between text-xs font-bold text-gray-400 uppercase tracking-widest">
                    <span>Subtotal</span><span>${{ number_format($order->subtotal, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-gray-400 uppercase tracking-widest">
                    <span>Descuento</span><span>- ${{ number_format($order->discount, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-white/10">
                    <span class="text-xs font-black uppercase italic">Total</span>
                    <span class="text-xl font-black text-primary italic">${{ number_format($order->total, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        @if($order->notes)
            <div class="bg-white dark:bg-dark-alt rounded-[32px] border border-gray-100 dark:border-white/5 p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Notas</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $order->notes }}</p>
            </div>
        @endif

        @if($canFulfill)
            <div class="flex flex-col md:flex-row gap-4">
                @if($isDraft)
                    <form action="{{ route('order.confirm', $order) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit"
                            class="w-full bg-amber-500 hover:bg-amber-600 text-white font-black py-4 rounded-2xl shadow-xl shadow-amber-500/25 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2">
                            <i data-lucide="thumbs-up" class="w-5 h-5"></i>
                            Confirmar Pedido
                        </button>
                    </form>
                @endif

                <button type="button" @click="showFulfillModal = true"
                    class="flex-1 bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-2xl shadow-xl shadow-primary/25 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2">
                    <i data-lucide="receipt" class="w-5 h-5"></i>
                    Facturar Pedido
                </button>

                <form action="{{ route('order.cancel', $order) }}" method="POST"
                    onsubmit="return confirm('¿Cancelar este pedido?');">
                    @csrf
                    <button type="submit"
                        class="px-8 bg-gray-100 dark:bg-dark text-gray-500 font-black py-4 rounded-2xl hover:bg-red-500 hover:text-white transition-all text-sm uppercase tracking-widest w-full">
                        Cancelar
                    </button>
                </form>
            </div>

            <!-- Fulfill Modal -->
            <div x-show="showFulfillModal" x-cloak
                class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-dark/60 backdrop-blur-sm animate-in fade-in duration-300">
                <div @click.away="showFulfillModal = false"
                    class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl w-full max-w-md p-10 space-y-6 animate-in zoom-in duration-300">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-black italic tracking-tight">Facturar Pedido</h3>
                        <button @click="showFulfillModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400">Esto crea la venta, descuenta el stock de cada producto y, si corresponde, carga el pago. Se valida el stock disponible en este momento.</p>
                    <form action="{{ route('order.fulfill', $order) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Método de Pago</label>
                            <select name="payment_method" required
                                class="w-full px-6 py-4 rounded-2xl bg-gray-900 dark:bg-dark text-white border border-transparent outline-none font-black italic appearance-none">
                                <option value="cash">Efectivo</option>
                                <option value="transfer">Transferencia</option>
                                <option value="card">Tarjeta Crédito/Débito</option>
                                <option value="account">Cuenta Corriente</option>
                            </select>
                        </div>
                        <button type="submit"
                            class="w-full bg-primary text-white font-black py-4 rounded-2xl shadow-xl hover:bg-primary/90 transition-all uppercase text-xs tracking-widest italic flex items-center justify-center gap-2">
                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                            Confirmar y Facturar
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
