@extends('layouts.admin')

@section('title', 'Compra ' . $purchase->code)

@section('content')
    @php
        $statusColors = ['pending' => 'bg-amber-500/10 text-amber-600', 'received' => 'bg-emerald-500/10 text-emerald-600', 'cancelled' => 'bg-gray-400/10 text-gray-500'];
        $statusLabels = ['pending' => 'Pendiente', 'received' => 'Recibida', 'cancelled' => 'Cancelada'];
        $statusValue = $purchase->status?->value ?? 'pending';
        $isPending = $statusValue === 'pending';
    @endphp

    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="fixed bottom-8 right-8 z-[100] bg-emerald-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="fixed bottom-8 right-8 z-[100] bg-red-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="truck" class="w-8 h-8 text-primary"></i> {{ $purchase->code }}
                    <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full {{ $statusColors[$statusValue] ?? '' }}">
                        {{ $statusLabels[$statusValue] ?? $statusValue }}
                    </span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    {{ $purchase->supplier->business_name }} · Cargada por {{ $purchase->creator->name ?? '—' }} el {{ $purchase->created_at->format('d/m/Y H:i') }}
                    @if($purchase->received_at)
                        · Recibida el {{ $purchase->received_at->format('d/m/Y H:i') }}
                    @endif
                </p>
            </div>
            <a href="{{ route('purchase.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Producto</th>
                            <th class="px-8 py-5 text-center">Cant.</th>
                            <th class="px-8 py-5 text-right">Costo Unit.</th>
                            <th class="px-8 py-5 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @foreach($purchase->items as $item)
                            <tr>
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $item->product->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter italic">{{ $item->product->internal_code }}</p>
                                </td>
                                <td class="px-8 py-6 text-center text-sm font-bold text-gray-600 dark:text-gray-400">{{ $item->quantity }}</td>
                                <td class="px-8 py-6 text-right text-sm font-bold text-gray-600 dark:text-gray-400">${{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-8 py-6 text-right text-sm font-black text-primary italic">${{ number_format($item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-8 bg-gray-50/30 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 space-y-2">
                <div class="flex justify-between text-xs font-bold text-gray-400 uppercase tracking-widest">
                    <span>Subtotal</span><span>${{ number_format($purchase->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-xs font-bold text-gray-400 uppercase tracking-widest">
                    <span>Descuento</span><span>- ${{ number_format($purchase->discount, 2) }}</span>
                </div>
                <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-white/10">
                    <span class="text-xs font-black uppercase italic">Total</span>
                    <span class="text-xl font-black text-primary italic">${{ number_format($purchase->total, 2) }}</span>
                </div>
            </div>
        </div>

        @if($purchase->notes)
            <div class="bg-white dark:bg-dark-alt rounded-[32px] border border-gray-100 dark:border-white/5 p-6">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2">Notas</p>
                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $purchase->notes }}</p>
            </div>
        @endif

        @if($isPending)
            <div class="flex flex-col md:flex-row gap-4">
                <form action="{{ route('purchase.receive', $purchase) }}" method="POST" class="flex-1"
                    onsubmit="return confirm('¿Confirmar la recepción? Esto va a sumar el stock de cada producto y actualizar su costo.');">
                    @csrf
                    <button type="submit"
                        class="w-full bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-2xl shadow-xl shadow-primary/25 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2">
                        <i data-lucide="package-check" class="w-5 h-5"></i>
                        Confirmar Recepción
                    </button>
                </form>
                <form action="{{ route('purchase.cancel', $purchase) }}" method="POST"
                    onsubmit="return confirm('¿Cancelar esta compra?');">
                    @csrf
                    <button type="submit"
                        class="px-8 bg-gray-100 dark:bg-dark text-gray-500 font-black py-4 rounded-2xl hover:bg-red-500 hover:text-white transition-all text-sm uppercase tracking-widest">
                        Cancelar Compra
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection
