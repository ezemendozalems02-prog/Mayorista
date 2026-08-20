@extends('layouts.admin')

@section('title', 'Compras')

@section('content')
    <div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
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

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center bg-white dark:bg-dark-alt p-1.5 rounded-2xl border border-gray-100 dark:border-white/5 shadow-sm w-full md:w-auto">
                <a href="{{ route('purchase.index') }}"
                    class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ !request('status') ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-400 hover:text-gray-600' }}">
                    Todas </a>
                <a href="{{ route('purchase.index', ['status' => 'pending']) }}"
                    class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ request('status') == 'pending' ? 'bg-amber-500 text-white shadow-lg shadow-amber-500/20' : 'text-gray-400 hover:text-gray-600' }}">
                    Pendientes </a>
                <a href="{{ route('purchase.index', ['status' => 'received']) }}"
                    class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ request('status') == 'received' ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' : 'text-gray-400 hover:text-gray-600' }}">
                    Recibidas </a>
            </div>

            <a href="{{ route('purchase.create') }}"
                class="bg-primary hover:bg-primary/90 text-white px-8 py-3 rounded-2xl shadow-xl shadow-primary/20 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 group w-full md:w-auto">
                <i data-lucide="truck" class="w-4 h-4 group-hover:rotate-6 transition-all"></i>
                Nueva Compra
            </a>
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Compra</th>
                            <th class="px-8 py-5 text-left hidden md:table-cell">Proveedor</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Productos</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Total</th>
                            <th class="px-8 py-5 text-left">Estado</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($purchases as $purchase)
                            @php
                                $statusColors = ['pending' => 'bg-amber-500/10 text-amber-600', 'received' => 'bg-emerald-500/10 text-emerald-600', 'cancelled' => 'bg-gray-400/10 text-gray-500'];
                                $statusLabels = ['pending' => 'Pendiente', 'received' => 'Recibida', 'cancelled' => 'Cancelada'];
                                $statusValue = $purchase->status?->value ?? 'pending';
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all group">
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $purchase->code }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter italic">{{ $purchase->created_at->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-gray-500 dark:text-gray-400 italic hidden md:table-cell">
                                    {{ $purchase->supplier->business_name ?? '—' }}
                                </td>
                                <td class="px-8 py-6 hidden sm:table-cell">
                                    <span class="text-[10px] font-black uppercase bg-primary/10 text-primary px-3 py-1 rounded-full border border-primary/20 shadow-sm">
                                        {{ $purchase->items_count }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-sm font-black text-gray-700 dark:text-gray-200 italic hidden sm:table-cell">
                                    ${{ number_format($purchase->total, 2, ',', '.') }}
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full {{ $statusColors[$statusValue] ?? '' }}">
                                        {{ $statusLabels[$statusValue] ?? $statusValue }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <a href="{{ route('purchase.show', $purchase) }}"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-50 dark:bg-dark hover:bg-primary hover:text-white transition-all rounded-xl border border-transparent shadow-sm text-xs font-black uppercase tracking-widest">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="truck" class="w-16 h-16 mb-4 opacity-20"></i>
                                        <p class="text-lg font-black tracking-tight italic">No hay compras todavía</p>
                                        <p class="text-xs uppercase font-bold mt-1 opacity-50 tracking-widest">Registrá tu primer pedido a un proveedor</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-8 bg-gray-50/30 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
@endsection
