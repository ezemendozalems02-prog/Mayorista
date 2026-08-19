@extends('layouts.admin')

@section('title', 'Nueva Marca')

@section('content')
    <div class="max-w-2xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="tag" class="w-8 h-8 text-primary"></i> Nueva Marca
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Registrá una marca para asociarla a tus productos.</p>
            </div>
            <a href="{{ route('brand.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-12">
            <form action="{{ route('brand.store') }}" method="POST" class="space-y-8">
                @csrf

                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                        placeholder="Ej: ABC">
                    @error('name') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-5 h-5 rounded-lg text-primary focus:ring-primary">
                    <label for="is_active" class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Marca activa</label>
                </div>

                <div class="pt-6 border-t border-gray-100 dark:border-white/5 flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-2xl shadow-xl shadow-primary/25 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2 group">
                        <i data-lucide="check-circle" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                        Guardar Marca
                    </button>
                    <a href="{{ route('brand.index') }}"
                        class="px-8 bg-gray-100 dark:bg-dark text-gray-500 font-black py-4 rounded-2xl hover:bg-gray-200 dark:hover:bg-white/5 transition-all text-sm uppercase tracking-widest text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
