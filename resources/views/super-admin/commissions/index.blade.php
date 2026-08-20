@extends('layouts.admin')

@section('title', 'Control de Comisiones')

@section('content')
<div class="space-y-8 pb-20">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="dollar-sign" class="w-8 h-8 text-emerald-500"></i> Control de Comisiones
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">
                Aprobación y seguimiento de pagos para afiliados y socios comerciales.
            </p>
        </div>
    </div>

    {{-- Commissions Table --}}
    <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/5 dark:shadow-none overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-widest italic">
                        <th class="px-8 py-5">Afiliado</th>
                        <th class="px-8 py-5">Nuevo Negocio</th>
                        <th class="px-8 py-5 text-center">Monto</th>
                        <th class="px-8 py-5 text-center">Estado</th>
                        <th class="px-8 py-5 text-center">Fecha</th>
                        <th class="px-8 py-5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    @forelse($commissions as $commission)
                    <tr class="group hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all">
                        <td class="px-8 py-6">
                            <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $commission->affiliate->name }}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $commission->affiliate->affiliate_code }}</p>
                        </td>
                        <td class="px-8 py-6">
                            <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">
                                {{ $commission->referral->organization->name ?? 'N/A' }}
                            </p>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="text-sm font-black text-emerald-600 italic tracking-tighter">USD {{ number_format($commission->amount, 2, ',', '.') }}</span>
                        </td>
                        <td class="px-8 py-6 text-center">
                             <span class="text-[9px] px-3 py-1 rounded-full font-black uppercase tracking-widest {{ $commission->status === 'paid' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-amber-500/10 text-amber-600' }}">
                                {{ $commission->status === 'paid' ? 'Pagado' : 'Pendiente' }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-center text-xs text-gray-500 font-medium">
                            {{ $commission->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-8 py-6 text-right">
                            @if($commission->status === 'pending')
                            <form action="{{ route('super-admin.commissions.approve', $commission) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-emerald-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all shadow-lg shadow-emerald-500/20">
                                    Aprobar Pago
                                </button>
                            </form>
                            @else
                                <span class="text-[10px] font-black text-gray-300 uppercase italic">Procesado</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-12 text-center text-gray-400 italic">No hay comisiones registradas aún.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($commissions->hasPages())
        <div class="px-8 py-4 bg-gray-50/50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
            {{ $commissions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
