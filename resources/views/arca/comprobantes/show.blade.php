@extends('layouts.admin')
@section('title', 'Factura ' . $invoice->tipo_comprobante)

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-20 animate-in fade-in slide-in-from-bottom-5 duration-500">

    {{-- Back + Header --}}
    <div>
        <a href="{{ route('arca.comprobantes') }}"
            class="flex items-center gap-1.5 text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 mb-4 transition-colors w-fit">
            <i data-lucide="chevron-left" class="w-4 h-4"></i> Volver a Comprobantes
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-black italic flex items-center gap-2">
                        <i data-lucide="file-text" class="w-6 h-6 text-violet-500"></i>
                        Factura {{ $invoice->tipo_comprobante }} —
                        @if($invoice->numero_comprobante)
                            {{ str_pad($invoice->punto_venta, 4, '0', STR_PAD_LEFT) }}-{{ str_pad($invoice->numero_comprobante, 8, '0', STR_PAD_LEFT) }}
                        @else
                            Sin número
                        @endif
                    </h1>
                    @php
                        $badge = match($invoice->estado) {
                            'AUTORIZADO' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                            'PENDIENTE'  => 'bg-gray-500/10 text-gray-500',
                            'RECHAZADO'  => 'bg-red-500/10 text-red-600 dark:text-red-400',
                            'ERROR'      => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                            default      => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-xl text-xs font-black uppercase tracking-widest {{ $badge }}">
                        {{ $invoice->estado }}
                    </span>
                </div>
                <p class="text-sm text-gray-400 font-medium">
                    Emitida el {{ $invoice->created_at->format('d \d\e F \d\e Y') }}
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap gap-2">
                @if(in_array($invoice->estado, ['PENDIENTE', 'ERROR']))
                <form action="{{ route('arca.autorizar', $invoice->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="btn-violet flex items-center gap-2 px-5 py-2.5 rounded-2xl font-black text-sm shadow-xl">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                        {{ $invoice->estado === 'ERROR' ? 'Reintentar' : 'Emitir con ARCA' }}
                    </button>
                </form>
                @endif
                @if($invoice->estado === 'AUTORIZADO')
                <form action="{{ route('arca.generar-pdf', $invoice->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-2xl border border-gray-200 dark:border-white/10 font-black text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i> Generar PDF
                    </button>
                </form>
                <a href="{{ route('arca.descargar-pdf', $invoice->id) }}"
                    class="btn-violet flex items-center gap-2 px-5 py-2.5 rounded-2xl font-black text-sm shadow-xl">
                    <i data-lucide="download" class="w-4 h-4"></i> Descargar PDF
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Cards row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Receptor --}}
        <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-6">
            <h2 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2">
                <i data-lucide="user" class="w-3.5 h-3.5"></i> Datos del Receptor
            </h2>
            <div class="space-y-0">
                @foreach([
                    ['Nombre', $invoice->cliente_nombre],
                    ['Documento', $invoice->cliente_documento ?? '—'],
                    ['Condición IVA', $invoice->cliente_condicion_iva ? str_replace('_', ' ', $invoice->cliente_condicion_iva) : '—'],
                ] as [$label, $value])
                <div class="flex justify-between items-center py-2.5 border-b border-gray-50 dark:border-white/5 last:border-0">
                    <span class="text-xs text-gray-500">{{ $label }}</span>
                    <span class="text-xs font-bold text-right">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Comprobante --}}
        <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-6">
            <h2 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2">
                <i data-lucide="receipt" class="w-3.5 h-3.5"></i> Datos del Comprobante
            </h2>
            <div class="space-y-0">
                @foreach([
                    ['Tipo', 'Factura ' . $invoice->tipo_comprobante],
                    ['Punto de venta', str_pad($invoice->punto_venta, 4, '0', STR_PAD_LEFT)],
                    ['Número', $invoice->numero_comprobante ? str_pad($invoice->numero_comprobante, 8, '0', STR_PAD_LEFT) : '—'],
                    ['Fecha', $invoice->created_at->format('d/m/Y')],
                ] as [$label, $value])
                <div class="flex justify-between items-center py-2.5 border-b border-gray-50 dark:border-white/5 last:border-0">
                    <span class="text-xs text-gray-500">{{ $label }}</span>
                    <span class="text-xs font-bold text-right">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-6">
        <h2 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-5 flex items-center gap-2">
            <i data-lucide="list" class="w-3.5 h-3.5"></i> Detalle de Productos / Servicios
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark rounded-xl">
                    <tr>
                        <th class="px-4 py-3 text-left text-[9px] font-black uppercase tracking-widest text-gray-400">Descripción</th>
                        <th class="px-4 py-3 text-right text-[9px] font-black uppercase tracking-widest text-gray-400">Cantidad</th>
                        <th class="px-4 py-3 text-right text-[9px] font-black uppercase tracking-widest text-gray-400">P. Unitario</th>
                        <th class="px-4 py-3 text-right text-[9px] font-black uppercase tracking-widest text-gray-400">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    @forelse($invoice->items as $item)
                    <tr>
                        <td class="px-4 py-3">{{ $item->descripcion }}</td>
                        <td class="px-4 py-3 text-right font-mono">{{ number_format($item->cantidad, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono">$ {{ number_format($item->precio_unitario, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-black font-mono">$ {{ number_format($item->total, 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">Sin ítems registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex justify-end mt-5 pt-4 border-t border-gray-100 dark:border-white/5">
            <div class="w-full max-w-xs space-y-1.5 text-sm">
                <div class="flex justify-between text-gray-500">
                    <span>Subtotal</span>
                    <span class="font-bold">$ {{ number_format($invoice->subtotal, 2, ',', '.') }}</span>
                </div>
                @if($invoice->tipo_comprobante !== 'C')
                <div class="flex justify-between text-gray-500">
                    <span>IVA (21%)</span>
                    <span class="font-bold">$ {{ number_format($invoice->iva, 2, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between font-black text-base border-t border-gray-200 dark:border-white/10 pt-2 mt-2">
                    <span>Total</span>
                    <span class="text-violet-600 dark:text-violet-400">$ {{ number_format($invoice->total, 2, ',', '.') }}</span>
                </div>
                @if($invoice->tipo_comprobante === 'C')
                <p class="text-xs text-gray-400">* IVA incluido en el total (no discriminado)</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ARCA Data (only when authorized) --}}
    @if($invoice->estado === 'AUTORIZADO' && $invoice->cae)
    <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-6">
        <h2 class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-4 flex items-center gap-2">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i> Datos ARCA
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-0 mb-4">
            @foreach([
                ['CAE N°', $invoice->cae],
                ['Vencimiento CAE', $invoice->cae_vencimiento ? $invoice->cae_vencimiento->format('d/m/Y') : '—'],
                ['Autorizado', $invoice->updated_at->format('d/m/Y H:i')],
            ] as [$label, $value])
            <div class="py-2.5 border-b border-gray-50 dark:border-white/5 sm:border-b-0 sm:border-r sm:last:border-r-0 sm:px-4 first:pl-0">
                <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">{{ $label }}</p>
                <p class="text-sm font-black mt-0.5 font-mono">{{ $value }}</p>
            </div>
            @endforeach
        </div>
        <div class="flex items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 font-black">
            <i data-lucide="check-circle" class="w-4 h-4"></i>
            Comprobante autorizado por ARCA
        </div>
    </div>
    @endif

    {{-- Error / Rejection --}}
    @if(in_array($invoice->estado, ['ERROR', 'RECHAZADO']) && $invoice->error_message)
    <div class="p-5 rounded-3xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/20 space-y-4">
        <div class="flex items-center gap-2 text-red-700 dark:text-red-400 font-black text-sm">
            <i data-lucide="alert-circle" class="w-4 h-4"></i>
            {{ $invoice->estado === 'RECHAZADO' ? 'Factura rechazada por ARCA' : 'Error al procesar' }}
        </div>
        <p class="text-sm text-red-600 dark:text-red-400">{{ $invoice->error_message }}</p>
        <form action="{{ route('arca.autorizar', $invoice->id) }}" method="POST">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-black text-sm transition-colors">
                <i data-lucide="refresh-cw" class="w-4 h-4"></i> Reintentar autorización
            </button>
        </form>
    </div>
    @endif

</div>
@endsection
