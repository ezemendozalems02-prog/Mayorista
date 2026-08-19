@extends('layouts.admin')

@section('title', 'Nuevo Producto')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="package-plus" class="w-8 h-8 text-primary"></i> Nuevo Producto
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">El código interno se genera automáticamente al guardar.</p>
            </div>
            <a href="{{ route('product.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-12">
            <form action="{{ route('product.store') }}" method="POST" class="space-y-10">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="Ej: Cuaderno Universitario ABC">
                        @error('name') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Categoría</label>
                        <select name="category_id"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                            <option value="">— Sin categoría —</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Marca</label>
                        <select name="brand_id"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                            <option value="">— Sin marca —</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Código de Barras</label>
                        <input type="text" name="barcode" value="{{ old('barcode') }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="Escaneá o ingresá manualmente">
                        @error('barcode') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Estado</label>
                        <select name="status"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Activo</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                            <option value="discontinued" {{ old('status') == 'discontinued' ? 'selected' : '' }}>Discontinuado</option>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Costo</label>
                        <input type="number" step="0.01" min="0" name="cost" value="{{ old('cost', 0) }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Precio Minorista</label>
                        <input type="number" step="0.01" min="0" name="retail_price" value="{{ old('retail_price') }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Precio Mayorista</label>
                        <input type="number" step="0.01" min="0" name="wholesale_price" value="{{ old('wholesale_price') }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Stock Mínimo</label>
                        <input type="number" step="1" min="0" name="min_stock" value="{{ old('min_stock', 0) }}"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Descripción</label>
                        <textarea name="description" rows="3"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">{{ old('description') }}</textarea>
                    </div>
                </div>

                @include('products.partials.suppliers-fieldset', ['suppliers' => $suppliers, 'selected' => []])

                <div class="pt-6 border-t border-gray-100 dark:border-white/5 flex gap-4">
                    <button type="submit"
                        class="flex-1 bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-2xl shadow-xl shadow-primary/25 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2 group">
                        <i data-lucide="check-circle" class="w-5 h-5 group-hover:scale-110 transition-transform"></i>
                        Guardar Producto
                    </button>
                    <a href="{{ route('product.index') }}"
                        class="px-8 bg-gray-100 dark:bg-dark text-gray-500 font-black py-4 rounded-2xl hover:bg-gray-200 dark:hover:bg-white/5 transition-all text-sm uppercase tracking-widest text-center">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
