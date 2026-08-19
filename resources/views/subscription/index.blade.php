@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
        <!-- Header -->
        <div class="text-center mb-10 md:mb-16 animate-in fade-in slide-in-from-top-4 duration-700">
            <h1
                class="text-3xl sm:text-4xl md:text-5xl font-black text-gray-900 dark:text-gray-100 tracking-tight italic mb-4">
                Elegí el plan perfecto para <span class="text-primary">tu negocio</span>
            </h1>
            <p class="text-gray-500 dark:text-gray-400 font-bold uppercase tracking-widest text-[10px] sm:text-xs italic">
                @if($trialDaysLeft > 0)
                    Te quedan <span class="text-emerald-500">{{ $trialDaysLeft }} días</span> de prueba gratuita.
                @else
                    Tu prueba ha expirado. Suscribite para continuar usando Vortex Control Phone.
                @endif
            </p>
        </div>

        <!-- Pricing Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto mb-20">
            @foreach($plans as $plan)
                <div class="relative group animate-in fade-in slide-in-from-bottom-8 duration-700"
                    style="animation-delay: {{ $loop->index * 150 }}ms">
                    @if($plan['popular'] ?? false)
                        <div class="absolute -top-5 left-1/2 -translate-x-1/2 z-10">
                            <span
                                class="bg-primary text-white text-[10px] font-black uppercase tracking-widest px-4 py-2 rounded-full shadow-lg shadow-primary/30">
                                Recomendado para Negocios
                            </span>
                        </div>
                    @endif

                    <div
                        class="h-full bg-white dark:bg-dark-alt rounded-[40px] p-10 border {{ ($plan['popular'] ?? false) ? 'border-primary shadow-2xl shadow-primary/10' : 'border-gray-100 dark:border-white/5' }} transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl flex flex-col">
                        <div class="mb-8">
                            <div
                                class="w-14 h-14 bg-{{ $plan['color'] }}-500/10 rounded-2xl flex items-center justify-center text-{{ $plan['color'] }}-500 mb-6 group-hover:scale-110 transition-transform duration-500">
                                <i data-lucide="{{ $plan['id'] === 'basic' ? 'zap' : 'shield-check' }}" class="w-8 h-8"></i>
                            </div>
                            <h3 class="text-2xl font-black text-gray-900 dark:text-gray-100 italic mb-2">{{ $plan['name'] }}
                            </h3>

                            <div class="mb-4">
                                <div class="flex items-baseline gap-1">
                                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">USD</span>
                                    <span class="text-4xl font-black text-gray-900 dark:text-gray-100 italic tracking-tighter transition-all">
                                        ${{ $plan['price_usd'] }}
                                    </span>
                                    <span class="text-gray-400 font-bold text-sm uppercase">/mes</span>
                                    
                                    @if($plan['on_sale'])
                                        <div class="ml-auto flex flex-col items-end">
                                            <span class="text-xs font-black text-red-500 line-through decoration-2 opacity-60">USD ${{ $plan['original_price_usd'] }}</span>
                                            <span class="bg-emerald-500 text-white text-[9px] font-black px-2 py-0.5 rounded-md uppercase tracking-widest animate-pulse">¡REBAJA!</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="mt-2 flex items-center gap-2">
                                    <span
                                        class="px-3 py-1 bg-emerald-500/10 text-emerald-500 text-[10px] font-black rounded-xl border border-emerald-500/20">~
                                        ${{ number_format($plan['price_ars'], 0, ',', '.') }} ARS</span>
                                    <span class="text-[9px] text-gray-400 font-black uppercase tracking-widest opacity-50">(Dólar Blue)</span>
                                </div>
                            </div>
                        </div>

                        <ul class="space-y-4 mb-10 flex-1">
                            @foreach($plan['features'] as $feature)
                                <li class="flex items-center gap-3 text-sm font-bold text-gray-500 dark:text-gray-400 italic">
                                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-500"></i>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>

                        <form action="{{ route('subscription.checkout') }}" method="GET" class="space-y-3">
                            <input type="hidden" name="plan_id" value="{{ $plan['id'] }}">

                            <div class="grid grid-cols-1 gap-3">
                                @if($currentPlanSlug === $plan['id'] && $organization->subscription_status === 'active')
                                    <div class="w-full py-4 bg-emerald-500/10 text-emerald-600 rounded-2xl font-black uppercase text-xs tracking-widest flex items-center justify-center gap-2 border border-emerald-500/20">
                                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        Tu Plan Actual
                                    </div>
                                @else
                                    <button type="submit" name="method" value="mercadopago"
                                        class="w-full py-4 bg-blue-500 text-white rounded-2xl font-black uppercase text-xs tracking-widest transition-all active:scale-95 shadow-xl shadow-blue-500/20 hover:bg-blue-600 flex items-center justify-center gap-2">
                                        <img src="{{ asset('img/mercado_pago_logo.png') }}" class="h-4 brightness-0 invert"
                                            alt="MP">
                                        Mercado Pago
                                    </button>

                                    <div class="w-full py-4 bg-gray-100 dark:bg-white/5 text-gray-400 rounded-2xl font-black uppercase text-[10px] tracking-widest flex items-center justify-center gap-2 cursor-not-allowed opacity-60 border border-dashed border-gray-300 dark:border-white/10"
                                        title="Estamos trabajando en esta integración">
                                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                                        Stripe (Próximamente)
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Payment Gateways -->
        <div
            class="bg-white dark:bg-dark-alt rounded-[40px] p-12 border border-gray-100 dark:border-white/5 shadow-xl animate-in fade-in duration-1000 delay-500">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="max-w-md">
                    <h4 class="text-xl font-black text-gray-900 dark:text-gray-100 italic mb-2">Pasarelas de Pago Seguras
                    </h4>
                    <p class="text-sm font-bold text-gray-400 italic">Utilizamos tecnología de cifrado de punta para
                        proteger tus transacciones.</p>
                </div>
                <div
                    class="flex flex-wrap justify-center gap-8 grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all duration-500">
                    <img src="{{ asset('img/mercado_pago_logo.png') }}" class="h-6 md:h-8 object-contain"
                        alt="Mercado Pago">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/ba/Stripe_Logo%2C_revised_2016.svg/1200px-Stripe_Logo%2C_revised_2016.svg.png"
                        class="h-8 md:h-10 object-contain" alt="Stripe">
                    <img src="{{ asset('img/visa_logo.png') }}" class="h-4 md:h-6 object-contain" alt="Visa">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png"
                        class="h-8 md:h-10 object-contain" alt="Mastercard">
                </div>
            </div>
        </div>
    </div>
@endsection