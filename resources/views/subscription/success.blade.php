@extends('layouts.admin')

@section('content')
<div class="min-h-[70vh] flex flex-col items-center justify-center text-center px-4">
    <div class="w-24 h-24 bg-emerald-500/10 rounded-full flex items-center justify-center text-emerald-500 mb-8 animate-bounce">
        <i data-lucide="check-circle" class="w-12 h-12"></i>
    </div>

    <h1 class="text-4xl font-black text-gray-900 dark:text-gray-100 tracking-tight italic mb-4">
        ¡Pago <span class="text-primary">Recibido!</span>
    </h1>

    <p class="max-w-md text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest text-xs leading-relaxed italic mb-8">
        Tu suscripción está siendo procesada. En unos minutos tu cuenta se actualizará automáticamente con el nuevo plan.
    </p>

    <div class="flex flex-col sm:flex-row gap-4">
        <a href="{{ route('dashboard') }}" class="px-8 py-4 bg-primary text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-primary/20 hover:scale-105 transition-all">
            Ir al Dashboard
        </a>
        <a href="{{ route('subscription.index') }}" class="px-8 py-4 bg-white dark:bg-dark-alt text-gray-900 dark:text-gray-100 border border-gray-100 dark:border-white/5 rounded-2xl font-black uppercase text-xs tracking-widest hover:bg-gray-50 dark:hover:bg-white/5 transition-all">
            Ver mi suscripción
        </a>
    </div>
</div>
@endsection
