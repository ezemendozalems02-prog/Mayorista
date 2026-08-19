@extends('layouts.admin')

@section('title', 'Editar Plan: ' . $plan->name)

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500 pb-20">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="edit-3" class="w-8 h-8 text-red-500"></i> Plan: {{ $plan->name }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight italic">Configurá precios y ofertas especiales.</p>
        </div>
        <a href="{{ route('super-admin.plans.index') }}"
            class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
            <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-500/10 border border-red-500/20 p-6 rounded-3xl">
            <ul class="list-disc list-inside text-sm text-red-600 font-bold">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl p-10 md:p-12 relative overflow-hidden">

        <div class="absolute right-0 top-0 w-48 h-48 bg-red-500/5 rounded-bl-[100px]"></div>

        <form action="{{ route('super-admin.plans.update', $plan) }}" method="POST" class="space-y-8 relative">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Name -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Nombre del Plan <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <i data-lucide="type" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-red-500 transition-colors"></i>
                        <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                            class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-red-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>
                </div>

                <!-- Price Normal -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Precio USD (Base) <span class="text-red-500">*</span></label>
                    <div class="relative group">
                        <i data-lucide="dollar-sign" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-red-500 transition-colors"></i>
                        <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" required
                            class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-red-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-2 col-span-full">
                    <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Descripción / Eslogan</label>
                    <textarea name="description" rows="3"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-red-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">{{ old('description', $plan->description) }}</textarea>
                </div>

                <!-- Promo Settings Header -->
                <div class="col-span-full pt-6 border-t border-gray-100 dark:border-white/5">
                    <h3 class="text-sm font-black text-emerald-500 uppercase tracking-widest italic flex items-center gap-2">
                        <i data-lucide="tag" class="w-5 h-5"></i> Configuración de Oferta (Opcional)
                    </h3>
                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase italic">Si se deja el precio promo vacío, se usará el precio normal.</p>
                </div>

                <!-- Promo Price -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-emerald-500/70 dark:text-emerald-500/50 uppercase tracking-widest ml-1 italic">Precio Promo USD</label>
                    <div class="relative group">
                        <i data-lucide="percent" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-emerald-500 group-focus-within:text-emerald-600 transition-colors"></i>
                        <input type="number" step="0.01" name="promo_price" value="{{ old('promo_price', $plan->promo_price) }}"
                            class="w-full pl-12 pr-4 py-4 rounded-2xl bg-emerald-500/5 dark:bg-emerald-500/5 border border-transparent focus:border-emerald-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="Ej: 77.00">
                    </div>
                </div>

                <!-- Promo Ends At -->
                <div class="space-y-2">
                    <label class="text-xs font-black text-emerald-500/70 dark:text-emerald-500/50 uppercase tracking-widest ml-1 italic">Vencimiento de Promo</label>
                    <div class="relative group">
                        <i data-lucide="calendar" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-emerald-500 group-focus-within:text-emerald-600 transition-colors"></i>
                        <input type="datetime-local" name="promo_ends_at" 
                            value="{{ old('promo_ends_at', $plan->promo_ends_at ? $plan->promo_ends_at->format('Y-m-d\TH:i') : '') }}"
                            class="w-full pl-12 pr-4 py-4 rounded-2xl bg-emerald-500/5 dark:bg-emerald-500/5 border border-transparent focus:border-emerald-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>
                </div>

                <!-- Active Toggle -->
                <input type="hidden" name="is_active" value="1">
            </div>

            <div class="pt-10 border-t border-gray-100 dark:border-white/5 flex gap-4">
                <button type="submit"
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-red-500/20 active:scale-95 transition-all text-sm uppercase tracking-[0.2em] flex items-center justify-center gap-2 group">
                    <i data-lucide="save" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                    Guardar Cambios del Plan
                </button>
                <a href="{{ route('super-admin.plans.index') }}"
                    class="px-8 py-4 bg-gray-100 dark:bg-dark text-gray-400 rounded-2xl text-xs font-black uppercase tracking-widest hover:text-red-500 transition-colors italic border border-transparent flex items-center justify-center">
                    Cancelar
                </a>
            </div>

        </form>

    </div>

</div>
@endsection
