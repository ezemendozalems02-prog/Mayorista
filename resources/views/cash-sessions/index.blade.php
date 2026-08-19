@extends('layouts.admin')

@section('title', 'Caja')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

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

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="landmark" class="w-8 h-8 text-primary"></i> Caja
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    {{ $session ? 'Turno abierto por ' . $session->openedBy?->name : 'No hay ningún turno abierto' }}
                </p>
            </div>
            <a href="{{ route('cash-session.history') }}"
                class="inline-flex items-center gap-2 px-5 py-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all shadow-sm text-xs font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">
                <i data-lucide="history" class="w-4 h-4"></i>
                Historial
            </a>
        </div>

        @if(!$session)
            <!-- Abrir caja -->
            <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-10">
                <h2 class="text-sm font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-6">Abrir Turno</h2>
                <form action="{{ route('cash-session.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div class="space-y-2 md:col-span-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Fondo Inicial</label>
                        <input type="number" step="0.01" min="0" name="opening_amount" value="{{ old('opening_amount', 0) }}" required
                            class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold text-sm">
                    </div>
                    <div class="space-y-2 md:col-span-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Notas</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Opcional"
                            class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold text-sm">
                    </div>
                    <div class="md:col-span-1">
                        <button type="submit"
                            class="w-full bg-primary hover:bg-primary/90 text-white font-black py-3 rounded-xl shadow-lg shadow-primary/25 active:scale-95 transition-all text-xs uppercase tracking-widest flex items-center justify-center gap-2">
                            <i data-lucide="lock-open" class="w-4 h-4"></i>
                            Abrir Caja
                        </button>
                    </div>
                </form>
                @error('opening_amount') <p class="text-[10px] text-red-500 font-bold mt-3">{{ $message }}</p> @enderror
            </div>
        @else
            <!-- Balance -->
            <div class="bg-primary rounded-[40px] p-8 text-white shadow-2xl relative overflow-hidden">
                <i data-lucide="landmark" class="absolute -right-4 -bottom-4 w-32 h-32 opacity-10"></i>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] italic opacity-70">Saldo Esperado en Caja</p>
                <p class="text-4xl font-black italic tracking-tighter mt-1">${{ number_format($balance, 2) }}</p>
                <p class="text-xs font-bold opacity-70 mt-3">
                    Fondo inicial: ${{ number_format($session->opening_amount, 2) }} ·
                    Abierta el {{ $session->opened_at->format('d/m/Y H:i') }}
                </p>
            </div>

            <!-- Ingreso / Egreso manual -->
            <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-10">
                <h2 class="text-sm font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-6">Ingreso / Egreso Manual</h2>
                <form action="{{ route('cash-session.movements.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div class="space-y-2 md:col-span-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Tipo</label>
                        <select name="type" required
                            class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold text-sm">
                            <option value="income">Ingreso</option>
                            <option value="expense">Egreso</option>
                        </select>
                    </div>
                    <div class="space-y-2 md:col-span-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Monto</label>
                        <input type="number" step="0.01" min="0.01" name="amount" required
                            class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold text-sm">
                    </div>
                    <div class="space-y-2 md:col-span-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Notas</label>
                        <input type="text" name="notes" placeholder="Ej: Retiro para vuelto"
                            class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold text-sm">
                    </div>
                    <div class="md:col-span-1">
                        <button type="submit"
                            class="w-full bg-gray-800 dark:bg-white/10 hover:bg-gray-900 dark:hover:bg-white/20 text-white font-black py-3 rounded-xl shadow-lg active:scale-95 transition-all text-xs uppercase tracking-widest flex items-center justify-center gap-2">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i>
                            Registrar
                        </button>
                    </div>
                </form>
                @error('amount') <p class="text-[10px] text-red-500 font-bold mt-3">{{ $message }}</p> @enderror
            </div>

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
                                            <p class="text-sm font-black tracking-tight italic">Todavía no hay movimientos en este turno</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Cerrar caja -->
            <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-10">
                <h2 class="text-sm font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2">Cerrar Turno (Arqueo)</h2>
                <p class="text-xs text-gray-400 mb-6">Contá el efectivo físico del cajón y cargalo acá. El sistema calcula la diferencia contra lo esperado (${{ number_format($balance, 2) }}).</p>
                <form action="{{ route('cash-session.close') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end"
                    onsubmit="return confirm('¿Cerrar la caja? No vas a poder registrar más movimientos en este turno.');">
                    @csrf
                    <div class="space-y-2 md:col-span-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Contado Físicamente</label>
                        <input type="number" step="0.01" min="0" name="counted_amount" required
                            class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold text-sm">
                    </div>
                    <div class="space-y-2 md:col-span-1">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Notas</label>
                        <input type="text" name="notes" placeholder="Opcional"
                            class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold text-sm">
                    </div>
                    <div class="md:col-span-1">
                        <button type="submit"
                            class="w-full bg-red-500 hover:bg-red-600 text-white font-black py-3 rounded-xl shadow-lg shadow-red-500/25 active:scale-95 transition-all text-xs uppercase tracking-widest flex items-center justify-center gap-2">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                            Cerrar Caja
                        </button>
                    </div>
                </form>
                @error('counted_amount') <p class="text-[10px] text-red-500 font-bold mt-3">{{ $message }}</p> @enderror
            </div>
        @endif
    </div>
@endsection
