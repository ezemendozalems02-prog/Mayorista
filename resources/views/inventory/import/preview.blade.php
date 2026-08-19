@extends('layouts.admin')

@section('title', 'Vista Previa Importación')

@section('content')
<div class="max-w-7xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="eye" class="w-8 h-8 text-indigo-500"></i> Vista <span class="text-indigo-500 tracking-tighter">Previa</span>
            </h1>
            <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-1">Revisa los datos antes de insertarlos.</p>
        </div>
        <div class="flex gap-4">
            <a href="{{ route('inventory.import.index') }}" class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-indigo-500 transition-colors flex items-center gap-2">
                <i data-lucide="x" class="w-4 h-4"></i> Cancelar
            </a>
            
            <form action="{{ route('inventory.import.store') }}" method="POST" id="importForm">
                @csrf
                <input type="hidden" name="file_path" value="{{ $filePath }}">
                <input type="hidden" name="branch_id" value="{{ $branchId }}">
                @foreach($mapping as $dbField => $fileHeaderIndex)
                    <input type="hidden" name="mapping[{{ $dbField }}]" value="{{ $fileHeaderIndex }}">
                @endforeach
                <button type="button" onclick="confirmImport()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-2xl shadow-xl shadow-indigo-500/20 text-xs font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-4 h-4"></i> Confirmar Importación
                </button>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-dark-alt rounded-[30px] p-6 border border-gray-100 dark:border-white/5 shadow-2xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-transparent dark:from-indigo-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-2 relative z-10">Total Filas</p>
            <p class="text-4xl font-black text-gray-900 dark:text-white tracking-tighter relative z-10">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-500/5 rounded-[30px] p-6 border border-emerald-100 dark:border-emerald-500/10 shadow-lg shadow-emerald-500/5 relative overflow-hidden">
            <p class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-2">Válidas</p>
            <p class="text-4xl font-black text-emerald-600 tracking-tighter">{{ $summary['valid'] }}</p>
        </div>
        <div class="bg-red-50 dark:bg-red-500/5 rounded-[30px] p-6 border border-red-100 dark:border-red-500/10 shadow-lg shadow-red-500/5 relative overflow-hidden">
            <p class="text-[10px] font-black uppercase tracking-widest text-red-600 mb-2">Con Errores</p>
            <p class="text-4xl font-black text-red-600 tracking-tighter">{{ $summary['invalid'] }}</p>
        </div>
    </div>

    @if($summary['invalid'] > 0)
        <div class="bg-amber-50 dark:bg-amber-500/10 border-l-4 border-amber-500 p-6 rounded-2xl">
            <div class="flex items-start gap-4">
                <i data-lucide="alert-triangle" class="w-6 h-6 text-amber-600 shrink-0"></i>
                <div>
                    <h4 class="text-sm font-black text-amber-800 dark:text-amber-400 tracking-tight">Advertencia: Filas inválidas detectadas</h4>
                    <p class="text-xs text-amber-700 dark:text-amber-500/80 font-medium mt-1">Las filas con errores no serán importadas. Solo se procesarán las {{ $summary['valid'] }} filas válidas. Puedes revisar los detalles a continuación.</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Data Table -->
    <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-white/5 text-gray-400 font-bold uppercase text-[10px] tracking-widest italic">
                        <th class="px-6 py-4">Fila</th>
                        <th class="px-6 py-4">Estado</th>
                        <th class="px-6 py-4">Marca / Modelo</th>
                        <th class="px-6 py-4">Specs</th>
                        <th class="px-6 py-4">PV (Precio Venta)</th>
                        <th class="px-6 py-4 max-w-xs">Errores</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 font-medium">
                    @foreach($preview as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors {{ !$item['is_valid'] ? 'bg-red-50/30 dark:bg-red-500/5' : '' }}">
                            <td class="px-6 py-4 text-xs font-bold text-gray-500">{{ $item['row_index'] }}</td>
                            <td class="px-6 py-4">
                                @if($item['is_valid'])
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                        Válido
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400 border border-red-200 dark:border-red-500/20">
                                        Inválido
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-black text-gray-900 dark:text-white">{{ $item['data']['brand'] ?? 'N/D' }}</span>
                                <span class="text-indigo-600 dark:text-indigo-400">{{ $item['data']['model'] ?? 'N/D' }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300">{{ $item['data']['storage'] ?? '-' }}</span>
                                    <span class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300">{{ $item['data']['color'] ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-black tracking-tighter text-indigo-600 dark:text-indigo-400">
                                ${{ $item['data']['sale_price'] ?? '0' }}
                            </td>
                            <td class="px-6 py-4 max-w-xs truncate text-[10px] text-red-500 font-bold uppercase tracking-widest">
                                @if(!$item['is_valid'])
                                    {{ implode(' | ', $item['errors']) }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function confirmImport() {
        if (confirm('¿Estás seguro de que quieres procesar esta importación? Las filas inválidas serán ignoradas.')) {
            const btn = document.querySelector('button[type="button"]');
            btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Procesando...';
            btn.disabled = true;
            document.getElementById('importForm').submit();
        }
    }
</script>
@endsection
