@extends('layouts.admin')

@section('title', 'Resultado Importación')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700 text-center">
    
    @if($result['success'] > 0)
        <!-- Éxito -->
        <div class="w-24 h-24 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center shadow-2xl shadow-emerald-500/30 animate-bounce mt-10">
            <i data-lucide="check-circle-2" class="w-12 h-12 text-emerald-600 dark:text-emerald-400"></i>
        </div>
        
        <div>
            <h1 class="text-4xl font-black tracking-tight text-gray-900 dark:text-white mt-6 mb-2">¡Importación Completada!</h1>
            <p class="text-lg text-gray-500 dark:text-gray-400 font-medium tracking-tight">Se han importado exitosamente {{ $result['success'] }} unidades a tu inventario.</p>
        </div>
    @else
        <!-- Fallo Total -->
        <div class="w-24 h-24 mx-auto rounded-full bg-red-100 dark:bg-red-500/20 flex items-center justify-center shadow-2xl shadow-red-500/30 mt-10">
            <i data-lucide="alert-circle" class="w-12 h-12 text-red-600 dark:text-red-400"></i>
        </div>
        
        <div>
            <h1 class="text-4xl font-black tracking-tight text-gray-900 dark:text-white mt-6 mb-2">Sin Resultados</h1>
            <p class="text-lg text-gray-500 dark:text-gray-400 font-medium tracking-tight">No se pudo importar ninguna unidad. Verifica los errores en el paso anterior.</p>
        </div>
    @endif

    <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl overflow-hidden p-8 max-w-lg mx-auto mt-8">
        <h4 class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-6 italic text-center">Resumen de la Operación</h4>
        
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 dark:bg-white/5 p-4 rounded-2xl flex flex-col items-center">
                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-1">Total Procesado</p>
                <p class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter">{{ $result['total'] }}</p>
            </div>
            <div class="bg-red-50 dark:bg-red-500/10 p-4 rounded-2xl flex flex-col items-center">
                <p class="text-[10px] text-red-500 uppercase tracking-widest font-bold mb-1">Ignorados/Error</p>
                <p class="text-3xl font-black text-red-600 tracking-tighter">{{ $result['failed'] }}</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mt-8">
        <a href="{{ route('inventory.index') }}" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl shadow-xl shadow-indigo-500/20 text-xs font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-3">
            <i data-lucide="package" class="w-4 h-4"></i> Ir al Inventario
        </a>
        <a href="{{ route('inventory.import.index') }}" class="w-full sm:w-auto bg-white dark:bg-dark-alt hover:bg-gray-50 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300 px-8 py-4 rounded-2xl shadow-lg border border-gray-100 dark:border-white/5 text-xs font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-3">
            <i data-lucide="upload" class="w-4 h-4"></i> Importar Más
        </a>
    </div>

</div>
@endsection
