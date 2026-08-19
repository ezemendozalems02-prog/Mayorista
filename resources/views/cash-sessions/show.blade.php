@extends('layouts.admin')

@section('title', 'Caja #' . $session->id)

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-8 right-8 z-[100] bg-emerald-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="landmark" class="w-8 h-8 text-primary"></i> Turno #{{ $session->id }}
                    @if($session->isOpen())
                        <span class="text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-600 px-3 py-1 rounded-full border border-emerald-500/20">Abierta</span>
                    @else
                        <span class="text-[10px] font-black uppercase bg-gray-400/10 text-gray-500 px-3 py-1 rounded-full">Cerrada</span>
                    @endif
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    Abierta por {{ $session->openedBy?->name ?? '—' }} el {{ $session->opened_at->format('d/m/Y H:i') }}
                    @if($session->closed_at)
                        · Cerrada por {{ $session->closedBy?->name ?? '—' }} el {{ $session->closed_at->format('d/m/Y H:i') }}
                    @endif
                </p>
            </div>
            <a href="{{ $session->isOpen() ? route('cash-session.index') : route('cash-session.history') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Fondo Inicial</p>
                <p class="text-xl font-black italic text-gray-800 dark:text-gray-100">${{ number_format($session->opening_amount, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 p-6 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">{{ $session->isOpen() ? 'Esperado (Ahora)' : 'Esperado al Cierre' }}</p>
                <p class="text-xl font-black italic text-gray-800 dark:text-gray-100">${{ number_format($session->isOpen() ? $balance : $session->expected_amount, 2) }}</p>
            </div>
            @if(!$session->isOpen())
                <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 p-6 shadow-sm">
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-1">Contado</p>
                    <p class="text-xl font-black italic text-gray-800 dark:text-gray-100">${{ number_format($session->closing_amount, 2) }}</p>
                </div>
                <div class="{{ $session->difference == 0 ? 'bg-emerald-500' : 'bg-red-500' }} rounded-3xl p-6 shadow-sm text-white">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-80 mb-1">Diferencia</p>
                    <p class="text-xl font-black italic">{{ $session->difference > 0 ? '+' : '' }}${{ number_format($session->difference, 2) }}</p>
                </div>
            @endif
        </div>

        @if($session->notes)
            <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-5 text-xs text-gray-500 dark:text-gray-400 italic">
                {{ $session->notes }}
            </div>
        @endif

        <!-- Movements -->
        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Fecha</th>
                            <th class="px-8 py-5 text-left">Motivo</th>
                            <th class="px-8 py-5 text-left">Monto</th>
                            <th class="px-8 py-5 text-left hidden md:table-cell">Usuario</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Notas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @php
                            $typeLabels = ['opening' => 'Apertura', 'income' => 'Ingreso Manual', 'expense' => 'Egreso Manual', 'sale' => 'Venta en Efectivo', 'account_payment' => 'Cobro Cta. Cte.'];
                        @endphp
                        @forelse($session->movements as $movement)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all">
                                <td class="px-8 py-5 text-xs font-bold text-gray-500 dark:text-gray-400">
                                    {{ $movement->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-8 py-5 text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-tight">
                                    {{ $typeLabels[$movement->type?->value] ?? $movement->type?->value }}
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-xs font-black {{ $movement->amount >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                        {{ $movement->amount >= 0 ? '+' : '' }}${{ number_format($movement->amount, 2) }}
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-xs font-bold text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                    {{ $movement->user?->name ?? '—' }}
                                </td>
                                <td class="px-8 py-5 text-xs text-gray-400 italic hidden sm:table-cell">
                                    {{ $movement->notes ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="landmark" class="w-12 h-12 mb-3 opacity-20"></i>
                                        <p class="text-sm font-black tracking-tight italic">Sin movimientos en este turno</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
