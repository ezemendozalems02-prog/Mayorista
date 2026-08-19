@extends('layouts.admin')

@section('title', 'Editar Proveedor')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="edit-3" class="w-8 h-8 text-primary"></i> Editar Proveedor
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Actualizá los datos de contacto del proveedor.</p>
            </div>
            <a href="{{ route('supplier.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-12">
            <form action="{{ route('supplier.update', $supplier) }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Razón Social <span class="text-red-500">*</span></label>
                        <input type="text" name="business_name" value="{{ old('business_name', $supplier->business_name) }}" required
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                        @error('business_name') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Nombre de Fantasía</label>
                        <input type="text" name="trade_name" value="{{ old('trade_name', $supplier->trade_name) }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">CUIT</label>
                        <input type="text" name="cuit" value="{{ old('cuit', $supplier->cuit) }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $supplier->whatsapp) }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Dirección</label>
                        <input type="text" name="address" value="{{ old('address', $supplier->address) }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Notas</label>
                        <textarea name="notes" rows="3"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">{{ old('notes', $supplier->notes) }}</textarea>
                    </div>

                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                            class="w-5 h-5 rounded-lg text-primary focus:ring-primary">
                        <label for="is_active" class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest">Proveedor activo</label>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 dark:border-white/5 flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-2xl shadow-xl shadow-primary/25 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2 group">
                        <i data-lucide="check" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                        Guardar Cambios
                    </button>
                    <a href="{{ route('supplier.index') }}"
                        class="px-8 bg-gray-100 dark:bg-dark text-gray-500 font-black py-4 rounded-2xl hover:bg-gray-200 dark:hover:bg-white/5 transition-all text-sm uppercase tracking-widest text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
