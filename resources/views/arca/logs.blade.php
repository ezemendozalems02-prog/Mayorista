@extends('layouts.admin')
@section('title', 'Logs ARCA')

@section('content')
<div class="space-y-6 pb-20 animate-in fade-in slide-in-from-bottom-5 duration-500"
    x-data="{ modal: null, loadingId: null,
        async openLog(id) {
            this.loadingId = id;
            const res = await fetch('{{ route('arca.show-log', '__ID__') }}'.replace('__ID__', id), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const json = await res.json();
            this.modal = json;
            this.loadingId = null;
        },
        maskSensitive(obj) {
            if (!obj) return obj;
            const keys = ['Token', 'Sign', 'token', 'sign', 'private_key', 'certificate', 'Clave'];
            const masked = JSON.parse(JSON.stringify(obj));
            function walk(o) {
                if (typeof o !== 'object' || o === null) return;
                for (const k of Object.keys(o)) {
                    if (keys.includes(k)) o[k] = '[REDACTED]';
                    else walk(o[k]);
                }
            }
            walk(masked);
            return masked;
        }
    }">

    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
            <i data-lucide="activity" class="w-8 h-8 text-violet-500"></i>
            Logs ARCA
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1">Registro técnico de comunicaciones con ARCA / AFIP</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('arca.logs') }}" class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="space-y-1">
                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Endpoint</label>
                <input type="text" name="endpoint" value="{{ request('endpoint') }}" placeholder="Buscar..."
                    class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-dark border border-transparent text-sm font-bold outline-none">
            </div>
            <div class="space-y-1">
                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Estado HTTP</label>
                <input type="number" name="status" value="{{ request('status') }}" placeholder="200, 422..."
                    class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-dark border border-transparent text-sm font-bold outline-none">
            </div>
            <div class="space-y-1">
                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-dark border border-transparent text-sm font-bold outline-none">
            </div>
            <div class="flex items-end gap-2">
                <div class="flex-1 space-y-1">
                    <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="w-full px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-dark border border-transparent text-sm font-bold outline-none">
                </div>
                <button type="submit" class="btn-violet px-4 py-2.5 rounded-xl font-black text-xs uppercase tracking-widest mb-0.5">
                    Filtrar
                </button>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm overflow-hidden">
        @if($logs->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-gray-400">
            <i data-lucide="activity" class="w-12 h-12 mb-4 opacity-30"></i>
            <p class="font-bold text-sm">No hay registros técnicos de ARCA por el momento.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-dark border-b border-gray-100 dark:border-white/5">
                    <tr>
                        <th class="px-5 py-4 text-left text-[9px] font-black uppercase tracking-widest text-gray-400">#</th>
                        <th class="px-5 py-4 text-left text-[9px] font-black uppercase tracking-widest text-gray-400">Endpoint</th>
                        <th class="px-5 py-4 text-center text-[9px] font-black uppercase tracking-widest text-gray-400">HTTP</th>
                        <th class="px-5 py-4 text-left text-[9px] font-black uppercase tracking-widest text-gray-400">Comprobante</th>
                        <th class="px-5 py-4 text-left text-[9px] font-black uppercase tracking-widest text-gray-400">Fecha</th>
                        <th class="px-5 py-4 text-center text-[9px] font-black uppercase tracking-widest text-gray-400">Detalle</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    @foreach($logs as $log)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/2 transition-colors">
                        <td class="px-5 py-3.5 text-gray-400 text-xs font-mono">{{ $log->id }}</td>
                        <td class="px-5 py-3.5 font-mono text-xs max-w-[200px] truncate" title="{{ $log->endpoint }}">
                            {{ $log->endpoint }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            @php
                                $code = $log->status ?? 0;
                                $color = $code >= 200 && $code < 300 ? 'bg-emerald-500/10 text-emerald-600' : ($code >= 400 ? 'bg-red-500/10 text-red-600' : 'bg-gray-500/10 text-gray-500');
                            @endphp
                            <span class="inline-block px-2.5 py-1 rounded-xl text-[10px] font-black {{ $color }}">
                                {{ $code ?: '—' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-500">
                            @if($log->invoice)
                                <a href="{{ route('arca.show-factura', $log->invoice->id) }}"
                                    class="font-mono hover:text-violet-600 transition-colors">
                                    F{{ $log->invoice->tipo_comprobante }}
                                    {{ str_pad($log->invoice->numero_comprobante ?? 0, 8, '0', STR_PAD_LEFT) }}
                                </a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-gray-400 whitespace-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <button @click="openLog({{ $log->id }})"
                                class="p-2 rounded-xl text-gray-400 hover:text-violet-600 hover:bg-violet-500/10 transition-colors relative">
                                <span x-show="loadingId === {{ $log->id }}" class="absolute inset-0 flex items-center justify-center">
                                    <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                </span>
                                <i data-lucide="eye" class="w-4 h-4" :class="loadingId === {{ $log->id }} ? 'opacity-0' : ''"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 dark:border-white/5 flex justify-between items-center">
            <span class="text-xs font-bold text-gray-400">
                {{ $logs->firstItem() }}–{{ $logs->lastItem() }} de {{ $logs->total() }}
            </span>
            <div class="flex gap-2">
                @if(!$logs->onFirstPage())
                    <a href="{{ $logs->previousPageUrl() }}" class="px-4 py-2 rounded-xl bg-gray-50 dark:bg-dark text-xs font-bold hover:bg-gray-100 transition-colors">← Anterior</a>
                @endif
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="px-4 py-2 rounded-xl bg-gray-50 dark:bg-dark text-xs font-bold hover:bg-gray-100 transition-colors">Siguiente →</a>
                @endif
            </div>
        </div>
        @endif
        @endif
    </div>

    {{-- Log Detail Modal --}}
    <div x-show="modal" x-cloak
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-dark/60 backdrop-blur-sm"
        @keydown.escape.window="modal = null">
        <div @click.away="modal = null"
            class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100 dark:border-white/5 flex-shrink-0">
                <h3 class="font-black italic text-lg flex items-center gap-2">
                    <i data-lucide="activity" class="w-5 h-5 text-violet-500"></i>
                    <span x-text="'Log #' + (modal?.id || '')"></span>
                </h3>
                <button @click="modal = null" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="overflow-y-auto p-6 space-y-5 flex-1">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Endpoint</p>
                        <p class="font-mono text-xs break-all" x-text="modal?.endpoint"></p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Estado HTTP</p>
                        <p class="font-black text-sm" x-text="modal?.status || '—'"></p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-1">Fecha</p>
                        <p class="text-xs" x-text="modal?.created_at"></p>
                    </div>
                </div>
                <div x-show="modal?.request_payload">
                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">Request</p>
                    <pre class="bg-gray-50 dark:bg-dark rounded-2xl p-4 text-xs font-mono whitespace-pre-wrap break-all overflow-auto max-h-52"
                        x-text="JSON.stringify(maskSensitive(modal?.request_payload), null, 2)"></pre>
                </div>
                <div x-show="modal?.response_payload">
                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-400 mb-2">Response</p>
                    <pre class="bg-gray-50 dark:bg-dark rounded-2xl p-4 text-xs font-mono whitespace-pre-wrap break-all overflow-auto max-h-52"
                        x-text="JSON.stringify(maskSensitive(modal?.response_payload), null, 2)"></pre>
                </div>
                <div x-show="modal?.error_message">
                    <p class="text-[9px] font-black uppercase tracking-widest text-red-400 mb-2">Error</p>
                    <p class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/20 rounded-2xl px-4 py-3" x-text="modal?.error_message"></p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
