@extends('layouts.admin')

@section('title', 'Importar Stock')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="upload-cloud" class="w-8 h-8 text-indigo-500"></i> Importación <span class="text-indigo-500 tracking-tighter">Masiva</span>
            </h1>
            <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-1">Carga de inventario vía Excel, CSV o Texto Plano.</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('inventory.import.template') }}" class="text-xs font-black uppercase tracking-widest text-emerald-600 hover:text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 px-4 py-2 rounded-xl transition-colors flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i> Bajar Plantilla Base
            </a>
            <a href="{{ route('inventory.index') }}" class="text-xs font-black uppercase tracking-widest text-gray-400 hover:text-indigo-500 transition-colors flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Volver al Inventario
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- File Upload -->
        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl overflow-hidden group">
            <div class="p-8 space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center border border-indigo-100 dark:border-indigo-500/20 shadow-sm">
                        <i data-lucide="file-spreadsheet" class="w-6 h-6 text-indigo-500"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 tracking-tight">Cargar Archivo</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Excel (.xlsx) o CSV (.csv)</p>
                    </div>
                </div>

                <form action="{{ route('inventory.import.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div class="relative group/drop">
                        <input type="file" name="file" id="file" class="hidden" accept=".csv,.xlsx">
                        <label for="file" class="flex flex-col items-center justify-center w-full h-48 rounded-[30px] border-2 border-dashed border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/[0.02] hover:bg-indigo-50/50 dark:hover:bg-indigo-500/5 hover:border-indigo-500/30 transition-all cursor-pointer">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i data-lucide="upload" class="w-10 h-10 text-gray-300 dark:text-gray-600 group-hover/drop:text-indigo-500 group-hover/drop:scale-110 transition-all mb-4"></i>
                                <p class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest" id="file-name">Selecciona un archivo</p>
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-4 rounded-2xl shadow-xl shadow-indigo-500/20 text-xs font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-3">
                        <i data-lucide="zap" class="w-4 h-4"></i> Analizar Archivo
                    </button>
                </form>
            </div>
        </div>

        <!-- Raw Text Import -->
        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl overflow-hidden group">
            <div class="p-8 space-y-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center border border-amber-100 dark:border-amber-500/20 shadow-sm">
                        <i data-lucide="align-left" class="w-6 h-6 text-amber-500"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 tracking-tight">Texto Plano</h3>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Pega tus datos (CSV style)</p>
                    </div>
                </div>

                <form action="{{ route('inventory.import.upload') }}" method="POST" class="space-y-6">
                    @csrf
                    <textarea name="raw_text" rows="6" 
                        class="w-full p-6 rounded-[30px] bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5 outline-none focus:border-amber-500 focus:ring-4 focus:ring-amber-500/5 transition-all text-xs font-mono font-medium placeholder-gray-400"
                        placeholder="Marca,Modelo,IMEI,Precio Venta&#10;Apple,iPhone 13,35123...,800&#10;Apple,iPhone 14,35456...,950"></textarea>

                    <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white px-8 py-4 rounded-2xl shadow-xl shadow-amber-500/20 text-xs font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-3">
                        <i data-lucide="copy" class="w-4 h-4"></i> Procesar Texto
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Info / Tips -->
    <div class="bg-indigo-600/5 border border-indigo-500/10 rounded-[30px] p-8 flex gap-6 items-start">
        <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center shrink-0 shadow-lg shadow-indigo-600/20">
            <i data-lucide="lightbulb" class="w-6 h-6 text-white"></i>
        </div>
        <div class="space-y-2">
            <h4 class="text-sm font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest italic">Consejos para una importación exitosa</h4>
            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-medium">
                Asegúrate de que la primera fila contenga los nombres de las columnas. No te preocupes por el orden o los nombres exactos, podrás mapearlos en el siguiente paso. El sistema detectará automáticamente separadores como coma (,), punto y coma (;) o tabulaciones.
            </p>
        </div>
    </div>
</div>

<script>
    document.getElementById('file').addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name || 'Selecciona un archivo';
        document.getElementById('file-name').textContent = fileName;
        document.getElementById('file-name').classList.remove('text-gray-500');
        document.getElementById('file-name').classList.add('text-indigo-600');
    });
</script>
@endsection
