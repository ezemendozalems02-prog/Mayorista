@extends('layouts.admin')

@section('title', 'Historial de Caja')

@section('content')
    <div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

        <div class="flex items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="history" class="w-8 h-8 text-primary"></i> Historial de Caja
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Todos los turnos, abiertos y cerrados</p>
            </div>
            <a href="{{ route('cash-session.index') }}"
                class="bg-primary hover:bg-primary/90 text-white px-8 py-3 rounded-2xl shadow-xl shadow-primary/20 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 group w-full md:w-auto">
                <i data-lucide="landmark" class="w-4 h-4"></i>
                Ir a Caja
            </a>
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Turno</th>
                            <th class="px-8 py-5 text-left hidden md:table-cell">Abierta / Cerrada</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Fondo Inicial</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Diferencia</th>
                            <th class="px-8 py-5 text-left">Estado</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($sessions as $session)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all group">
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">Turno #{{ $session->id }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter italic">{{ $session->openedBy?->name ?? '—' }}</p>
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-gray-500 dark:text-gray-400 hidden md:table-cell">
                                    {{ $session->opened_at->format('d/m/Y H:i') }}
                                    @if($session->closed_at)
                                        <br><span class="text-gray-400">hasta {{ $session->closed_at->format('d/m/Y H:i') }}</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-sm font-black text-gray-700 dark:text-gray-200 italic hidden sm:table-cell">
                                    ${{ number_format($session->opening_amount, 2, ',', '.') }}
                                </td>
                                <td class="px-8 py-6 hidden sm:table-cell">
                                    @if($session->difference === null)
                                        <span class="text-xs text-gray-300 italic">—</span>
                                    @else
                                        <span class="text-sm font-black italic {{ $session->difference == 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                            {{ $session->difference > 0 ? '+' : '' }}${{ number_format($session->difference, 2, ',', '.') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6">
                                    @if($session->isOpen())
                                        <span class="text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-600 px-3 py-1 rounded-full border border-emerald-500/20">Abierta</span>
                                    @else
                                        <span class="text-[10px] font-black uppercase bg-gray-400/10 text-gray-500 px-3 py-1 rounded-full">Cerrada</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <a href="{{ route('cash-session.show', $session) }}"
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
                                        <i data-lucide="landmark" class="w-16 h-16 mb-4 opacity-20"></i>
                                        <p class="text-lg font-black tracking-tight italic">Todavía no abriste ninguna caja</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-8 bg-gray-50/30 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
                {{ $sessions->links() }}
            </div>
        </div>
    </div>
@endsection
