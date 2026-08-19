@extends('layouts.admin')

@section('title', 'Módulo en Desarrollo')

@section('content')
    <div
        class="h-[70vh] flex flex-col items-center justify-center text-center px-4 animate-in fade-in zoom-in duration-500">
        <div class="relative mb-8">
            <div class="absolute inset-0 bg-primary/20 blur-3xl rounded-full scale-150 animate-pulse"></div>
            <div
                class="relative w-32 h-32 bg-white dark:bg-dark-alt rounded-[40px] flex items-center justify-center border border-gray-100 dark:border-white/5 shadow-2xl">
                <i data-lucide="construction" class="w-16 h-16 text-primary animate-bounce"></i>
            </div>
        </div>

        <h1 class="text-4xl font-black text-gray-900 dark:text-gray-100 tracking-tight italic mb-4">
            Módulo en <span class="text-primary">Construcción</span>
        </h1>

        <p
            class="max-w-md text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest text-xs leading-relaxed italic">
            Estamos trabajando para integrar esta funcionalidad. El sistema de gestión de Mito Yamile se actualiza
            constantemente
            para ofrecerte la mejor experiencia.
        </p>

        <div class="mt-12 flex flex-col sm:flex-row items-center gap-4">
            <a href="{{ route('dashboard') }}"
                class="px-8 py-4 bg-primary text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-primary/20 hover:scale-105 transition-all active:scale-95">
                Volver al Dashboard
            </a>
            <button onclick="history.back()"
                class="px-8 py-4 bg-white dark:bg-dark-alt text-gray-400 rounded-2xl font-black uppercase text-xs tracking-widest border border-gray-100 dark:border-white/5 hover:bg-gray-50 transition-all">
                Anterior
            </button>
        </div>

        <div class="mt-20 grid grid-cols-3 gap-8 opacity-20 filter grayscale">
            <i data-lucide="zap" class="w-8 h-8 text-primary"></i>
            <i data-lucide="shield-check" class="w-8 h-8 text-primary"></i>
            <i data-lucide="cpu" class="w-8 h-8 text-primary"></i>
        </div>
    </div>
@endsection