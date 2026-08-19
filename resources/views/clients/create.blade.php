@extends('layouts.admin')

@section('title', 'Nuevo Cliente')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="user-plus" class="w-8 h-8 text-primary"></i> Registrar Cliente
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Completá los datos para agendar un nuevo
                    cliente en el sistema.</p>
            </div>
            <a href="{{ route('client.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <!-- Form Card -->
        <div
            class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-12">

            <form action="{{ route('client.store') }}" method="POST" class="space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <!-- Client Type -->
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Tipo de Cliente <span class="text-red-500">*</span></label>
                        <div class="flex items-center bg-gray-100 dark:bg-dark p-1.5 rounded-2xl w-full md:w-auto md:inline-flex">
                            <label class="flex-1 md:flex-none">
                                <input type="radio" name="client_type" value="retail" class="peer sr-only" {{ old('client_type', 'retail') == 'retail' ? 'checked' : '' }}>
                                <span class="block text-center px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest cursor-pointer text-gray-400 peer-checked:bg-primary peer-checked:text-white peer-checked:shadow-lg transition-all">Minorista</span>
                            </label>
                            <label class="flex-1 md:flex-none">
                                <input type="radio" name="client_type" value="wholesale" class="peer sr-only" {{ old('client_type') == 'wholesale' ? 'checked' : '' }}>
                                <span class="block text-center px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-widest cursor-pointer text-gray-400 peer-checked:bg-primary peer-checked:text-white peer-checked:shadow-lg transition-all">Mayorista</span>
                            </label>
                        </div>
                        @error('client_type') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Full Name -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Nombre
                            de Contacto <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <i data-lucide="user"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required autofocus
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="Ej: Juan Pérez">
                        </div>
                        @error('full_name') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Business Name -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Razón
                            Social (si es mayorista)</label>
                        <div class="relative group">
                            <i data-lucide="building-2"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                            <input type="text" name="business_name" value="{{ old('business_name') }}"
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="Ej: Jugueterías del Sur SRL">
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Teléfono
                            / WhatsApp</label>
                        <div class="relative group">
                            <i data-lucide="phone"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="Ej: +54 9 11 ...">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Email</label>
                        <div class="relative group">
                            <i data-lucide="mail"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="ejemplo@correo.com">
                        </div>
                    </div>

                    <!-- Document Number -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">DNI /
                            Pasaporte</label>
                        <div class="relative group">
                            <i data-lucide="credit-card"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                            <input type="text" name="document_number" value="{{ old('document_number') }}"
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="Número de identificación">
                        </div>
                    </div>

                    <!-- CUIT -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">CUIT</label>
                        <input type="text" name="cuit" value="{{ old('cuit') }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="20-12345678-9">
                        @error('cuit') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Address -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Dirección</label>
                        <input type="text" name="address" value="{{ old('address') }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <!-- Credit Limit -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Límite de Crédito</label>
                        <input type="number" step="0.01" min="0" name="credit_limit" value="{{ old('credit_limit') }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="Opcional">
                        <p class="text-[10px] text-gray-400 ml-1">Preparado para cuenta corriente; todavía no se aplica ningún bloqueo.</p>
                    </div>

                    <!-- Discount -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Descuento por Defecto (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="discount_percentage" value="{{ old('discount_percentage', 0) }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <!-- Price List -->
                    <div class="md:col-span-2 space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Lista de Precios</label>
                        <select name="price_list_id"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                            <option value="">— Sin lista (precio {{ old('client_type', 'retail') == 'wholesale' ? 'mayorista' : 'minorista' }} estándar) —</option>
                            @foreach($priceLists as $priceList)
                                <option value="{{ $priceList->id }}" {{ old('price_list_id') == $priceList->id ? 'selected' : '' }}>{{ $priceList->name }}</option>
                            @endforeach
                        </select>
                        @error('price_list_id') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Notes -->
                    <div class="md:col-span-2 space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Notas
                            Internas / Observaciones</label>
                        <textarea name="notes" rows="3"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="Cualquier detalle relevante del cliente...">{{ old('notes') }}</textarea>
                    </div>

                </div>

                <div class="pt-6 border-t border-gray-100 dark:border-white/5 flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-2xl shadow-xl shadow-primary/25 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2 group">
                        <i data-lucide="check-circle" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                        Guardar Cliente
                    </button>
                    <a href="{{ route('client.index') }}"
                        class="px-8 bg-gray-100 dark:bg-dark text-gray-500 font-black py-4 rounded-2xl hover:bg-gray-200 dark:hover:bg-white/5 transition-all text-sm uppercase tracking-widest text-center">
                        Cancelar
                    </a>
                </div>

            </form>

        </div>

    </div>
@endsection