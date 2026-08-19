@extends('layouts.admin')

@section('title', 'Nueva Sucursal')

@section('content')
<div class="max-w-2xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500 pb-20">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="store" class="w-8 h-8 text-violet-500"></i> Nueva Sucursal
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Registrá un nuevo local o punto de venta.</p>
        </div>
        <a href="{{ route('branch.index') }}"
            class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
            <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
        </a>
    </div>

    <!-- Form -->
    <form action="{{ route('branch.store') }}" method="POST" class="space-y-6">
        @csrf
        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-violet-500/5 p-8 md:p-10 space-y-6">
            <h2 class="text-xs font-black text-violet-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                <i data-lucide="info" class="w-4 h-4"></i> Información de la Sucursal
            </h2>

            <!-- Name -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 italic">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                    placeholder="Ej: Sucursal Centro, Local Palermo">
                @error('name') <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p> @enderror
            </div>

            <!-- Address -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 italic">Dirección</label>
                <input type="text" name="address" value="{{ old('address') }}"
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                    placeholder="Ej: Av. Corrientes 1234, CABA">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Phone -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 italic">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                        placeholder="+54 11 1234-5678">
                </div>

                <!-- Email -->
                <div class="space-y-2">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 italic">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                        placeholder="sucursal@tunegocio.com">
                </div>
            </div>

            <!-- Manager -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 italic">Encargado / Responsable</label>
                <input type="text" name="manager_name" value="{{ old('manager_name') }}"
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                    placeholder="Nombre del encargado">
            </div>

            <!-- Active Toggle -->
            <div class="flex items-center justify-between p-5 bg-gray-50 dark:bg-dark rounded-2xl">
                <div>
                    <p class="text-sm font-black text-gray-700 dark:text-gray-200">Sucursal Activa</p>
                    <p class="text-xs text-gray-400 font-medium mt-0.5">Las sucursales inactivas no reciben inventario ni ventas.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-violet-600"></div>
                </label>
            </div>

            <!-- Notes -->
            <div class="space-y-2">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 italic">Notas internas</label>
                <textarea name="notes" rows="3"
                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold resize-none"
                    placeholder="Notas sobre horarios, características, etc.">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="space-y-4">
            <button type="submit"
                class="w-full bg-violet-600 hover:bg-violet-700 text-white font-black py-5 rounded-3xl shadow-2xl shadow-violet-500/30 active:scale-95 transition-all text-xs uppercase tracking-[0.2em] flex items-center justify-center gap-3">
                <i data-lucide="save" class="w-5 h-5"></i>
                Crear Sucursal
            </button>
            <a href="{{ route('branch.index') }}"
                class="block w-full text-center py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-violet-500 transition-colors italic">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
