@extends('layouts.admin')

@section('title', 'Cuenta Corriente — ' . $client->display_name)

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

        @php $balance = $client->current_balance; @endphp

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="wallet" class="w-8 h-8 text-primary"></i> {{ $client->display_name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Cuenta corriente</p>
            </div>
            <a href="{{ route('client.show', $client) }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <!-- Balance -->
        <div class="{{ $balance > 0 ? 'bg-red-500' : ($balance < 0 ? 'bg-emerald-600' : 'bg-gray-700') }} rounded-[40px] p-8 text-white shadow-2xl relative overflow-hidden">
            <i data-lucide="wallet" class="absolute -right-4 -bottom-4 w-32 h-32 opacity-10"></i>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] italic opacity-70">
                {{ $balance > 0 ? 'Saldo Deudor' : ($balance < 0 ? 'Saldo a Favor' : 'Cuenta al Día') }}
            </p>
            <p class="text-4xl font-black italic tracking-tighter mt-1">${{ number_format(abs($balance), 2) }}</p>
            @if($client->credit_limit)
                <p class="text-xs font-bold opacity-70 mt-3">Límite de crédito: ${{ number_format($client->credit_limit, 2) }}</p>
            @endif
        </div>

        <!-- Register Payment -->
        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-10">
            <h2 class="text-sm font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-6">Registrar Pago</h2>
            <form action="{{ route('client.account.pay', $client) }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                @csrf
                <div class="space-y-2 md:col-span-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Monto</label>
                    <input type="number" step="0.01" min="0.01" name="amount" required
                        class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold text-sm">
                </div>
                <div class="space-y-2 md:col-span-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Método</label>
                    <select name="method" required
                        class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold text-sm">
                        <option value="cash">Efectivo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="card">Tarjeta</option>
                        <option value="crypto">Cripto</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div class="space-y-2 md:col-span-1">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Notas</label>
                    <input type="text" name="notes" placeholder="Opcional"
                        class="w-full px-4 py-3 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold text-sm">
                </div>
                <div class="md:col-span-1">
                    <button type="submit"
                        class="w-full bg-primary hover:bg-primary/90 text-white font-black py-3 rounded-xl shadow-lg shadow-primary/25 active:scale-95 transition-all text-xs uppercase tracking-widest flex items-center justify-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        Registrar
                    </button>
                </div>
            </form>
            @error('amount') <p class="text-[10px] text-red-500 font-bold mt-3">{{ $message }}</p> @enderror
        </div>

        <!-- History -->
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
                            $typeLabels = ['sale' => 'Venta a Cuenta', 'payment' => 'Pago', 'adjustment' => 'Ajuste', 'credit_note' => 'Nota de Crédito'];
                        @endphp
                        @forelse($movements as $movement)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all">
                                <td class="px-8 py-5 text-xs font-bold text-gray-500 dark:text-gray-400">
                                    {{ $movement->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-8 py-5 text-xs font-black text-gray-700 dark:text-gray-200 uppercase tracking-tight">
                                    {{ $typeLabels[$movement->type?->value] ?? $movement->type?->value }}
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-xs font-black {{ $movement->amount >= 0 ? 'text-red-500' : 'text-emerald-600' }}">
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
                                        <i data-lucide="wallet" class="w-12 h-12 mb-3 opacity-20"></i>
                                        <p class="text-sm font-black tracking-tight italic">Todavía no hay movimientos en esta cuenta</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-8 bg-gray-50/30 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
@endsection
