@extends('layouts.admin')

@section('title', 'Mi Negocio')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 animate-in transition-all duration-500 pb-20">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="building-2" class="w-8 h-8 text-primary"></i> Perfil del Negocio
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Gestioná la identidad de tu local y
                    preferencias globales.</p>
            </div>
        </div>



        <form action="{{ route('organization.update') }}" method="POST" class="space-y-8">
            @csrf
            @method('PATCH')

            <!-- General Settings -->
            <div
                class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl p-10 space-y-8">
                <h2 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                    <i data-lucide="settings" class="w-4 h-4"></i> Información General
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2 md:col-span-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nombre
                            Comercial</label>
                        <input type="text" name="name" value="{{ $organization->name }}"
                            class="w-full px-8 py-5 rounded-3xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-black italic text-lg shadow-inner">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Teléfono de
                            contacto</label>
                        <input type="text" name="phone" value="{{ $organization->phone }}"
                            class="w-full px-8 py-5 rounded-3xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Email
                            Público</label>
                        <input type="email" name="email" value="{{ $organization->email }}"
                            class="w-full px-8 py-5 rounded-3xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold">
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Moneda
                            Principal</label>
                        <select name="currency"
                            class="w-full px-8 py-5 rounded-3xl bg-gray-50 dark:bg-dark border border-transparent outline-none font-black italic appearance-none">
                            <option value="USD" {{ $organization->currency == 'USD' ? 'selected' : '' }}>USD - Dólar
                                Estadounidense</option>
                            <option value="ARS" {{ $organization->currency == 'ARS' ? 'selected' : '' }}>ARS - Peso Argentino
                            </option>
                            <option value="PYG" {{ $organization->currency == 'PYG' ? 'selected' : '' }}>PYG - Guaraní
                                Paraguayo</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">País</label>
                        <input type="text" name="country" value="{{ $organization->country }}"
                            class="w-full px-8 py-5 rounded-3xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold">
                    </div>
                </div>
            </div>

            <!-- Email Notifications -->
            <div
                class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl p-10 space-y-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                        <i data-lucide="mail" class="w-4 h-4"></i> Notificaciones por Email
                    </h2>
                    
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="notifications_email_enabled" value="1" class="sr-only peer" {{ $organization->notifications_email_enabled ? 'checked' : '' }}>
                        <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-primary rounded-full shadow-inner"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-1 gap-8">
                    <div class="space-y-4">
                        <div>
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Email de Recepción (Alias)</label>
                            <input type="email" name="notifications_email_alias" value="{{ $organization->notifications_email_alias }}" 
                                placeholder="Dejar vacío para usar el email principal ({{ $organization->email }})"
                                class="w-full px-8 py-5 rounded-3xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold shadow-inner">
                        </div>
                        <p class="text-[10px] font-bold text-gray-400 italic px-2">
                            * Si activás esta opción, recibirás un correo informativo cada vez que se realice una venta en tu negocio.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end">
                <button type="submit"
                    class="bg-primary hover:bg-primary/90 text-white px-12 py-5 rounded-3xl shadow-2xl shadow-primary/30 font-black uppercase tracking-[0.2em] italic text-xs transition-all active:scale-95 flex items-center gap-3">
                    <i data-lucide="save" class="w-5 h-5"></i>
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
@endsection