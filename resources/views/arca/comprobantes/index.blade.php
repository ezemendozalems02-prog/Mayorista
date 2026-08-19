@extends('layouts.admin')
@section('title', 'Comprobantes ARCA')

@section('content')
<div class="space-y-6 pb-20 animate-in fade-in slide-in-from-bottom-5 duration-500">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="file-text" class="w-8 h-8 text-violet-500"></i>
                Comprobantes
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1">Historial de facturas electrónicas ARCA</p>
        </div>
        <a href="{{ route('arca.nueva') }}"
            class="btn-violet px-6 py-3 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl flex items-center gap-2 w-fit">
            <i data-lucide="file-plus" class="w-4 h-4"></i> Nueva Factura
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('arca.comprobantes') }}" class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-5">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="space-y-1">
                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Estado</label>
                <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-dark border border-transparent text-sm font-bold outline-none">
                    <option value="">Todos</option>
                    @foreach(['PENDIENTE' => 'Pendiente', 'AUTORIZADO' => 'Autorizado', 'RECHAZADO' => 'Rechazado', 'ERROR' => 'Error'] as $val => $label)
                        <option value="{{ $val }}" {{ ($filters['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Tipo</label>
                <select name="type" class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-dark border border-transparent text-sm font-bold outline-none">
                    <option value="">Todos</option>
                    @foreach(['A' => 'Factura A', 'B' => 'Factura B', 'C' => 'Factura C'] as $val => $label)
                        <option value="{{ $val }}" {{ ($filters['type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1">
                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Desde</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                    class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-dark border border-transparent text-sm font-bold outline-none">
            </div>
            <div class="space-y-1">
                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Hasta</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                    class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-dark border border-transparent text-sm font-bold outline-none">
            </div>
            <div class="space-y-1">
                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Cliente</label>
                <input type="text" name="client" value="{{ $filters['client'] ?? '' }}" placeholder="Buscar..."
                    class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-dark border border-transparent text-sm font-bold outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-violet w-full py-2.5 rounded-xl font-black text-xs uppercase tracking-widest">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden">
        @if($invoices->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-gray-400">
            <i data-lucide="file-text" class="w-12 h-12 mb-4 opacity-30"></i>
            <p class="font-bold text-sm">No hay comprobantes aún.</p>
            <a href="{{ route('arca.nueva') }}" class="mt-4 text-violet-500 font-black text-sm hover:underline">
                Crear tu primera factura →
            </a>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark border-b border-gray-100 dark:border-white/5">
                    <tr>
                        <th class="px-5 py-4 text-left text-[9px] font-black uppercase tracking-widest text-gray-400">#</th>
                        <th class="px-5 py-4 text-left text-[9px] font-black uppercase tracking-widest text-gray-400">Tipo</th>
                        <th class="px-5 py-4 text-left text-[9px] font-black uppercase tracking-widest text-gray-400">Número</th>
                        <th class="px-5 py-4 text-left text-[9px] font-black uppercase tracking-widest text-gray-400">Cliente</th>
                        <th class="px-5 py-4 text-right text-[9px] font-black uppercase tracking-widest text-gray-400">Total</th>
                        <th class="px-5 py-4 text-center text-[9px] font-black uppercase tracking-widest text-gray-400">Estado</th>
                        <th class="px-5 py-4 text-left text-[9px] font-black uppercase tracking-widest text-gray-400">Fecha</th>
                        <th class="px-5 py-4 text-center text-[9px] font-black uppercase tracking-widest text-gray-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    @foreach($invoices as $inv)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                        <td class="px-5 py-4 text-gray-400 text-xs font-mono">{{ $inv->id }}</td>
                        <td class="px-5 py-4 font-black text-xs">Factura {{ $inv->tipo_comprobante }}</td>
                        <td class="px-5 py-4 font-mono text-xs text-gray-500">
                            @if($inv->numero_comprobante)
                                {{ str_pad($inv->punto_venta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($inv->numero_comprobante, 8, '0', STR_PAD_LEFT) }}
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-medium max-w-[180px] truncate">{{ $inv->cliente_nombre }}</td>
                        <td class="px-5 py-4 text-right font-black">
                            $ {{ number_format($inv->total, 2, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @php
                                $badge = match($inv->estado) {
                                    'AUTORIZADO' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                                    'PENDIENTE'  => 'bg-gray-500/10 text-gray-500',
                                    'RECHAZADO'  => 'bg-red-500/10 text-red-600 dark:text-red-400',
                                    'ERROR'      => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                                    default      => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            <span class="inline-block px-3 py-1 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $badge }}">
                                {{ $inv->estado }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-xs text-gray-400 whitespace-nowrap">
                            {{ $inv->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('arca.show-factura', $inv->id) }}"
                                    class="p-2 rounded-xl text-gray-400 hover:text-violet-600 hover:bg-violet-500/10 transition-colors" title="Ver detalle">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @if(in_array($inv->estado, ['PENDIENTE', 'ERROR']))
                                <form action="{{ route('arca.autorizar', $inv->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-xl text-gray-400 hover:text-emerald-600 hover:bg-emerald-500/10 transition-colors" title="Autorizar con ARCA">
                                        <i data-lucide="zap" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                                @if($inv->estado === 'AUTORIZADO')
                                <form action="{{ route('arca.generar-pdf', $inv->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-500/10 transition-colors" title="Generar PDF">
                                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                <a href="{{ route('arca.descargar-pdf', $inv->id) }}"
                                    class="p-2 rounded-xl text-gray-400 hover:text-indigo-600 hover:bg-indigo-500/10 transition-colors" title="Descargar PDF">
                                    <i data-lucide="download" class="w-4 h-4"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($invoices->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 dark:border-white/5 flex justify-between items-center text-sm">
            <span class="text-gray-400 text-xs font-bold">
                Mostrando {{ $invoices->firstItem() }}–{{ $invoices->lastItem() }} de {{ $invoices->total() }}
            </span>
            <div class="flex gap-2">
                @if($invoices->onFirstPage())
                    <span class="px-4 py-2 rounded-xl text-gray-300 dark:text-gray-600 font-bold cursor-not-allowed text-xs">← Anterior</span>
                @else
                    <a href="{{ $invoices->previousPageUrl() }}" class="px-4 py-2 rounded-xl bg-gray-50 dark:bg-dark text-gray-600 dark:text-gray-300 font-bold text-xs hover:bg-gray-100 transition-colors">← Anterior</a>
                @endif
                @if($invoices->hasMorePages())
                    <a href="{{ $invoices->nextPageUrl() }}" class="px-4 py-2 rounded-xl bg-gray-50 dark:bg-dark text-gray-600 dark:text-gray-300 font-bold text-xs hover:bg-gray-100 transition-colors">Siguiente →</a>
                @else
                    <span class="px-4 py-2 rounded-xl text-gray-300 dark:text-gray-600 font-bold cursor-not-allowed text-xs">Siguiente →</span>
                @endif
            </div>
        </div>
        @endif
        @endif
    </div>

</div>
@endsection
