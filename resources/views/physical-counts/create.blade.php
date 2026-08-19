@extends('layouts.admin')

@section('title', 'Nuevo Conteo Físico')

@section('content')
    <div class="max-w-2xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="clipboard-list" class="w-8 h-8 text-primary"></i> Nuevo Conteo Físico
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    Se toma una foto del stock actual de cada producto activo; después cargás lo contado y las diferencias se ajustan solas.
                </p>
            </div>
            <a href="{{ route('physical-count.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-12">
            <form action="{{ route('physical-count.store') }}" method="POST" class="space-y-8">
                @csrf

                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Categoría (opcional)</label>
                    <select name="category_id"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 ml-1">Si elegís una categoría, el conteo solo incluye los productos activos de esa categoría.</p>
                    @error('category_id') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Notas</label>
                    <textarea name="notes" rows="3" placeholder="Ej: Conteo mensual de agosto"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">{{ old('notes') }}</textarea>
                </div>

                <div class="pt-6 border-t border-gray-100 dark:border-white/5 flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-2xl shadow-xl shadow-primary/25 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2 group">
                        <i data-lucide="play" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                        Iniciar Conteo
                    </button>
                    <a href="{{ route('physical-count.index') }}"
                        class="px-8 bg-gray-100 dark:bg-dark text-gray-500 font-black py-4 rounded-2xl hover:bg-gray-200 dark:hover:bg-white/5 transition-all text-sm uppercase tracking-widest text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
