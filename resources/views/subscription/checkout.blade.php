@extends('layouts.admin')

@section('content')
<div class="h-[70vh] flex flex-col items-center justify-center text-center px-4 animate-in fade-in zoom-in duration-500">
    <div class="relative mb-8">
        <div class="absolute inset-0 bg-emerald-500/20 blur-3xl rounded-full scale-150 animate-pulse"></div>
        <div class="relative w-32 h-32 bg-white dark:bg-dark-alt rounded-[40px] flex items-center justify-center border border-gray-100 dark:border-white/5 shadow-2xl">
            <i data-lucide="credit-card" class="w-16 h-16 text-emerald-500 animate-pulse"></i>
        </div>
    </div>

    <h1 class="text-4xl font-black text-gray-900 dark:text-gray-100 tracking-tight italic mb-4">
        Checkout: <span class="text-primary">{{ $planName }}</span>
    </h1>

    <p class="max-w-md text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest text-xs leading-relaxed italic mb-8">
        @if($method === 'mercadopago')
            Estás por suscribirte al <span class="text-primary font-black">{{ $planName }}</span> por 
            <span class="text-emerald-500 font-black">${{ number_format($priceArs, 0, ',', '.') }} ARS/mes</span>.
            (Conversión a Dólar Blue).
        @else
            Estás por suscribirte al <span class="text-primary font-black">{{ $planName }}</span> por 
            <span class="text-emerald-500 font-black">${{ $priceUsd }} USD/mes</span>.
        @endif
    </p>

    <div class="flex flex-col items-center justify-center p-12 bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl mb-8 w-full max-w-md group">
        <form action="{{ route('subscription.process') }}" method="POST" class="w-full">
            @csrf
            <input type="hidden" name="plan_id" value="{{ $planId }}">
            <input type="hidden" name="method" value="{{ $method }}">

            @if($method === 'mercadopago')
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('img/mercado_pago_logo.png') }}" class="h-10 object-contain" alt="Mercado Pago">
                </div>
                <button type="submit" class="w-full py-4 bg-blue-500 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-blue-500/20 hover:scale-[1.02] active:scale-95 transition-all">
                    Pagar ${{ number_format($priceArs, 0, ',', '.') }} ARS
                </button>
            @else
                <div class="flex justify-center mb-6">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Stripe_Logo%2C_revised_2016.svg/1200px-Stripe_Logo%2C_revised_2016.svg.png" class="h-10 object-contain" alt="Stripe">
                </div>
                <button type="submit" class="w-full py-4 bg-indigo-600 text-white rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-indigo-600/20 hover:scale-[1.02] active:scale-95 transition-all">
                    Suscribirme con Tarjeta (USD ${{ $priceUsd }})
                </button>
            @endif
        </form>
        <p class="mt-4 text-[10px] text-gray-400 font-bold uppercase tracking-widest italic flex items-center gap-1">
            <i data-lucide="shield-check" class="w-3 h-3"></i> Pago Seguro y Cifrado
        </p>
    </div>

    <div class="mt-12">
        <a href="{{ route('subscription.index') }}" class="text-xs font-bold text-gray-400 uppercase tracking-widest hover:text-primary transition-colors italic">
            &larr; Volver a Planes
        </a>
    </div>
</div>
@endsection
