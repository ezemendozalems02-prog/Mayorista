@extends('layouts.admin')

@section('title', 'Mapeo de Columnas')

@section('content')
<div class="max-w-6xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="git-merge" class="w-8 h-8 text-indigo-500"></i> Mapeo de <span class="text-indigo-500 tracking-tighter">Columnas</span>
            </h1>
            <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-1">Conecta los datos de tu archivo con nuestra base.</p>
        </div>
        <a href="{{ route('inventory.import.index') }}" class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-indigo-500 transition-colors flex items-center gap-2">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Cancelar Importación
        </a>
    </div>

    <form action="{{ route('inventory.import.preview') }}" method="POST" class="space-y-8">
        @csrf
        <input type="hidden" name="file_path" value="{{ $filePath }}">

        <!-- Branch Selection (If feature enabled) -->
        @if(isset($branches) && $branches->count() > 0)
        <div class="p-6 rounded-[30px] bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 shadow-2xl relative overflow-hidden group">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center border border-indigo-100 dark:border-indigo-500/20 shadow-sm relative z-10">
                    <i data-lucide="store" class="w-5 h-5 text-indigo-500"></i>
                </div>
                <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 tracking-tight z-10">Asignar a Sucursal</h3>
            </div>
            
            <div class="relative z-10">
                <select name="branch_id" class="w-full md:w-1/2 p-4 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-sm font-bold text-gray-600 dark:text-gray-300">
                    <option value="">(Opcional) Seleccione una sucursal</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-3 flex items-center gap-1.5 opacity-60">
                    <i data-lucide="info" class="w-3 h-3"></i>
                    Si no seleccionas sucursal, los equipos quedarán sin asignar.
                </p>
            </div>
        </div>
        @endif

        @if(isset($branches) && $branches->count() === 0)
        <div class="p-6 rounded-[30px] bg-amber-50/30 dark:bg-amber-500/10 border border-amber-500/20 shadow-2xl relative overflow-hidden group">
            <div class="flex items-start gap-4 mb-2">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center border border-amber-100 dark:border-amber-500/20 shadow-sm relative z-10">
                    <i data-lucide="store-plus" class="w-5 h-5 text-amber-500"></i>
                </div>
                <div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 tracking-tight z-10">Sin Sucursales</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-medium">
                        Para asignar la importación a una sucursal, primero crea una.
                    </p>
                </div>
            </div>
            <a href="{{ route('branch.create') }}"
                class="inline-flex items-center gap-2 mt-4 bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-2xl shadow-lg shadow-amber-500/20 text-xs font-black uppercase tracking-widest transition-all active:scale-95">
                <i data-lucide="plus" class="w-4 h-4"></i> Crear Sucursal
            </a>
        </div>
        @endif

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl overflow-hidden relative">
            <div class="p-8">
                <div class="grid gap-6">
                    @foreach($dbFields as $field => $label)
                        <div class="flex flex-col md:flex-row md:items-center gap-4 p-4 rounded-3xl border border-gray-100 dark:border-white/5 bg-gray-50/30 dark:bg-white/[0.01] hover:bg-gray-50 dark:hover:bg-dark/50 transition-colors">
                            <div class="md:w-1/3">
                                <label class="text-sm font-black text-gray-700 dark:text-gray-300 flex items-center gap-2">
                                    {{ $label }}
                                    @if(in_array($field, ['model', 'sale_price']))
                                        <span class="text-[10px] bg-red-100 text-red-600 px-2 py-0.5 rounded-full uppercase tracking-tighter">* Requerido</span>
                                    @endif
                                </label>
                            </div>
                            <div class="md:w-2/3 flex items-center gap-3">
                                <i data-lucide="arrow-right" class="w-4 h-4 text-gray-300 hidden md:block"></i>
                                <select name="mapping[{{ $field }}]" 
                                    class="w-full p-4 rounded-2xl bg-white dark:bg-dark-alt border border-gray-200 dark:border-white/10 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/5 transition-all text-sm font-medium">
                                    <option value="">-- Ignorar Campo --</option>
                                    @foreach($headers as $index => $header)
                                        @php
                                            $normalizedHeader = $normalizedHeaders[$index] ?? '';
                                            $autoSelect = str_contains($normalizedHeader, $field) || str_contains($field, $normalizedHeader);
                                            // More aggressive auto-select
                                            if ($field == 'purchase_price' && str_contains($normalizedHeader, 'cost')) $autoSelect = true;
                                            if ($field == 'sale_price' && str_contains($normalizedHeader, 'precio')) $autoSelect = true;
                                        @endphp
                                        <option value="{{ $index }}" {{ $autoSelect ? 'selected' : '' }}>
                                            Columna: {{ $header }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="p-8 bg-gray-50 dark:bg-dark border-t border-gray-100 dark:border-white/5 flex items-center justify-between">
                <p class="text-xs text-gray-500 font-medium">
                    Previsualizaremos los datos antes de guardarlos.
                </p>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl shadow-xl shadow-indigo-500/20 text-xs font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-3">
                    Vista Previa <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        @if(!empty($sample))
        <div class="mt-8 rounded-[30px] overflow-hidden border border-gray-100 dark:border-white/5 relative">
            <div class="px-6 py-4 bg-gray-50/80 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 relative">
                <h4 class="text-xs font-black uppercase tracking-widest text-indigo-500 italic flex items-center gap-2">
                    <i data-lucide="eye" class="w-4 h-4"></i> Muestra de datos (Primeras filas)
                </h4>
            </div>
            <div class="overflow-x-auto bg-white dark:bg-dark">
                <table class="w-full text-left text-sm whitespace-nowrap">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 text-gray-500 dark:text-gray-400 font-bold uppercase text-[10px] tracking-widest">
                            @foreach($headers as $header)
                                <th class="px-6 py-4">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5 font-medium text-gray-700 dark:text-gray-300">
                        @foreach($sample as $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                                @foreach($headers as $index => $header)
                                    <td class="px-6 py-4">{{ $row[$index] ?? '' }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </form>
</div>
@endsection
