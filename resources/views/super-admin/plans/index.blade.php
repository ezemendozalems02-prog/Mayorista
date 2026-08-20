@extends('layouts.admin')

@section('title', 'Gestionar Planes')

@section('content')
<div class="space-y-8 pb-20">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="package" class="w-8 h-8 text-red-500"></i> Planes de Suscripción
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">
                Administrá los precios, ofertas y promociones de la plataforma.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 p-4 rounded-2xl text-emerald-500 font-black text-sm italic">
            {{ session('success') }}
        </div>
    @endif

    {{-- Plans Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($plans as $plan)
        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/5 dark:shadow-none p-10 relative overflow-hidden group">
            
            <div class="absolute right-0 top-0 w-32 h-32 bg-red-500/5 rounded-bl-[100px] group-hover:bg-red-500/10 transition-colors duration-500"></div>

            <div class="relative z-10 flex flex-col h-full">
                <div class="flex items-center justify-between mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-gray-50 dark:bg-dark flex items-center justify-center border border-gray-100 dark:border-white/10 group-hover:scale-110 transition-transform">
                        <i data-lucide="{{ $plan->slug === 'pro' ? 'shield-check' : 'zap' }}" class="w-8 h-8 text-red-500"></i>
                    </div>
                    @if($plan->isOnSale())
                        <span class="bg-emerald-500 text-white text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-widest animate-pulse">Oferta Activa</span>
                    @endif
                </div>

                <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 italic mb-2">{{ $plan->name }}</h3>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6">{{ $plan->slug }}</p>

                <div class="space-y-4 mb-10 flex-1">
                    <div class="flex flex-col">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic leading-none mb-1">Precio Normal</span>
                        <span class="text-3xl font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">USD {{ number_format($plan->price, 2, ',', '.') }}</span>
                    </div>

                    @if($plan->promo_price)
                    <div class="p-4 rounded-3xl bg-emerald-500/5 border border-emerald-500/10">
                        <span class="text-[10px] font-black text-emerald-500 uppercase tracking-widest italic leading-none mb-1 block">Precio Promo</span>
                        <span class="text-2xl font-black text-emerald-600 italic tracking-tighter">USD {{ number_format($plan->promo_price, 2, ',', '.') }}</span>
                        @if($plan->promo_ends_at)
                            <p class="text-[9px] font-bold text-gray-400 mt-2 uppercase">Vence: {{ $plan->promo_ends_at->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                    @endif
                </div>

                <a href="{{ route('super-admin.plans.edit', $plan) }}" class="w-full py-4 bg-gray-900 dark:bg-dark text-white text-center rounded-2xl font-black uppercase text-[10px] tracking-[0.2em] transition-all active:scale-95 shadow-xl shadow-gray-900/10 hover:bg-black">
                    Editar Configuración
                </a>
            </div>
        </div>
        @endforeach
    </div>

</div>
@endsection
