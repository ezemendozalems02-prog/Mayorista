@extends('layouts.admin')

@section('title', 'Gestión de Afiliados')

@section('content')
<div class="space-y-8 pb-20">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="users" class="w-8 h-8 text-indigo-500"></i> Programa de Afiliados
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">
                Gestión de socios, revendedores y seguimiento de referidos.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.commissions.index') }}" class="px-6 py-3 bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 transition-all flex items-center gap-2">
                <i data-lucide="dollar-sign" class="w-4 h-4 text-emerald-500"></i> Ver Comisiones
            </a>
        </div>
    </div>

    {{-- Affiliates Table --}}
    <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/5 dark:shadow-none overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-widest italic">
                        <th class="px-8 py-5">Socio / Afiliado</th>
                        <th class="px-8 py-5 text-center">Código</th>
                        <th class="px-8 py-5 text-center">Tipo</th>
                        <th class="px-8 py-5 text-center">Referidos</th>
                        <th class="px-8 py-5 text-center">Saldo Actual</th>
                        <th class="px-8 py-5 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    @forelse($affiliates as $affiliate)
                    <tr class="group hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all">
                        <td class="px-8 py-6">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center font-black text-indigo-600 text-[10px]">
                                    {{ substr($affiliate->name, 0, 2) }}
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $affiliate->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $affiliate->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <code class="text-xs bg-gray-100 dark:bg-dark px-3 py-1 rounded-lg font-bold text-indigo-500">{{ $affiliate->affiliate_code }}</code>
                        </td>
                        <td class="px-8 py-6 text-center">
                            <span class="text-[9px] font-black uppercase tracking-widest text-gray-400">{{ $affiliate->type }}</span>
                        </td>
                        <td class="px-8 py-6 text-center font-black text-gray-900 dark:text-gray-100">
                            {{ $affiliate->referrals_count }}
                        </td>
                        <td class="px-8 py-6 text-center font-black text-emerald-500 italic">
                            $ {{ number_format($affiliate->balance, 2) }}
                        </td>
                        <td class="px-8 py-6 text-center">
                             <span class="text-[9px] px-3 py-1 rounded-full font-black uppercase tracking-widest {{ $affiliate->is_active ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-600' }}">
                                {{ $affiliate->is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-12 text-center text-gray-400 italic">No hay afiliados registrados aún.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($affiliates->hasPages())
        <div class="px-8 py-4 bg-gray-50/50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
            {{ $affiliates->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
