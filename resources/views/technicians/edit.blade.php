@extends('layouts.admin')

@section('title', 'Editar Técnico')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="user-cog" class="w-8 h-8 text-blue-500"></i> Perfil de Técnico
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight italic">Actualizá la
                    información y especialidades de tu equipo.</p>
            </div>
            <a href="{{ route('technician.index') }}"
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
        <div
            class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-10 md:p-12 relative overflow-hidden">

            <div class="absolute right-0 top-0 w-48 h-48 bg-blue-500/5 rounded-bl-[100px]"></div>

            <form action="{{ route('technician.update', $technician) }}" method="POST" class="space-y-8 relative">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                    <!-- Full Name -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Nombre
                            Completo <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <i data-lucide="award"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="text" name="full_name" value="{{ old('full_name', $technician->full_name) }}"
                                required
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-blue-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="Ej: Carlos Técnico">
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Teléfono
                            / WhatsApp</label>
                        <div class="relative group">
                            <i data-lucide="phone"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="text" name="phone" value="{{ old('phone', $technician->phone) }}"
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-blue-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="Ej: +54 9 11 ...">
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Email
                            Laboral</label>
                        <div class="relative group">
                            <i data-lucide="mail"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="email" name="email" value="{{ old('email', $technician->email) }}"
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-blue-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="ejemplo@correo.com">
                        </div>
                    </div>

                    <!-- Specialties -->
                    <div class="space-y-2">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Especialidades
                            / Habilidades</label>
                        <div class="relative group">
                            <i data-lucide="wrench"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                            <input type="text" name="specialties" value="{{ old('specialties', $technician->specialties) }}"
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-blue-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="Ej: Microelectrónica, Glass, Swapping">
                        </div>
                    </div>

                    <!-- Type -->
                    <div class="space-y-2 col-span-full md:col-span-1">
                        <label
                            class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Tipo de
                            Staff <span class="text-red-500">*</span></label>
                        <div class="relative group">
                            <i data-lucide="user-cog"
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors z-10"></i>
                            <select name="type" required
                                class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-blue-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                <option value="technician" {{ old('type', $technician->type) == 'technician' ? 'selected' : '' }}>Técnico</option>
                                <option value="seller" {{ old('type', $technician->type) == 'seller' ? 'selected' : '' }}>Vendedor</option>
                                <option value="owner" {{ old('type', $technician->type) == 'owner' ? 'selected' : '' }}>Dueño / Admin</option>
                            </select>
                            <i data-lucide="chevron-down"
                                class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Active Toggle -->
                    <div
                        class="flex items-center gap-4 col-span-full bg-gray-50 dark:bg-dark p-6 rounded-3xl border border-gray-100 dark:border-white/5">
                        <div class="flex-1">
                            <h4 class="text-sm font-black text-gray-900 dark:text-gray-100 italic uppercase">Estado
                                Operativo</h4>
                            <p class="text-xs text-gray-500 font-medium">Si está inactivo, no podrá inicar sesión ni ser asignado a reparaciones.</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $technician->is_active) ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="w-14 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-dark-alt peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500">
                            </div>
                        </label>
                    </div>

                </div>

                <!-- Access Settings -->
                <div x-data="{ giveAccess: {{ (old('give_access') || $technician->user_id) ? 'true' : 'false' }}, hasAccount: {{ $technician->user_id ? 'true' : 'false' }} }" class="pt-6 border-t border-gray-100 dark:border-white/5 space-y-6">
                    <div class="flex items-center gap-3 bg-blue-500/5 p-4 rounded-2xl border border-blue-500/10">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="give_access" value="1" class="sr-only peer" x-model="giveAccess" :disabled="hasAccount">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-dark peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600" :class="hasAccount ? 'opacity-50' : ''"></div>
                        </label>
                        <div class="flex-1">
                            <span class="text-sm font-black text-gray-700 dark:text-gray-200 uppercase tracking-widest italic">Acceso al sistema</span>
                            <p class="text-[10px] text-gray-500 font-medium italic" x-show="!hasAccount">Permite que este usuario inicie sesión con su email y contraseña.</p>
                            <p class="text-[10px] text-emerald-500 font-bold italic" x-show="hasAccount">Este integrante ya tiene una cuenta activa.</p>
                        </div>
                        @if($technician->user_id)
                            <input type="hidden" name="give_access" value="1">
                        @endif
                    </div>

                    <div x-show="giveAccess" x-transition class="space-y-4 animate-in fade-in zoom-in-95 duration-300">
                        <div class="space-y-2">
                            <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">
                                <span x-text="hasAccount ? 'Nueva Contraseña (opcional)' : 'Contraseña de acceso *'"></span>
                            </label>
                            <div class="relative group">
                                <i data-lucide="key" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                                <input type="password" name="password" 
                                    class="w-full pl-12 pr-4 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-blue-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="Dejar vacío para no cambiar" :required="giveAccess && !hasAccount">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 dark:border-white/5 flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-500/25 active:scale-95 transition-all text-sm uppercase tracking-[0.2em] flex items-center justify-center gap-2 group">
                        <i data-lucide="save" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                        Guardar Cambios
                    </button>
                    <a href="{{ route('technician.index') }}"
                        class="px-8 py-4 bg-gray-100 dark:bg-dark text-gray-400 rounded-2xl text-xs font-black uppercase tracking-widest hover:text-red-500 transition-colors italic border border-transparent flex items-center justify-center">
                        Cancelar
                    </a>
                </div>

            </form>

        </div>

    </div>
@endsection