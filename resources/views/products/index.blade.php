@extends('layouts.admin')

@section('title', 'Productos')

@section('content')
    <div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-8 right-8 z-[100] bg-emerald-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                class="fixed bottom-8 right-8 z-[100] bg-red-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <form action="{{ route('product.index') }}" method="GET" class="flex-1 flex flex-col md:flex-row items-center gap-4 w-full">
                <div class="relative w-full md:w-72 group">
                    <i data-lucide="search"
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                    <input name="search" value="{{ request('search') }}" type="text" placeholder="Nombre, código o código de barras..."
                        class="w-full pl-11 pr-4 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none focus:border-primary transition-all shadow-sm text-sm font-medium">
                </div>

                <select name="category_id" onchange="this.form.submit()"
                    class="w-full md:w-48 px-4 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none focus:border-primary transition-all shadow-sm text-sm font-medium">
                    <option value="">Todas las categorías</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>

                <select name="brand_id" onchange="this.form.submit()"
                    class="w-full md:w-48 px-4 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none focus:border-primary transition-all shadow-sm text-sm font-medium">
                    <option value="">Todas las marcas</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>

                <button type="submit" class="hidden">Filtrar</button>
            </form>

            <a href="{{ route('product.create') }}"
                class="bg-primary hover:bg-primary/90 text-white px-8 py-3 rounded-2xl shadow-xl shadow-primary/20 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 group w-full md:w-auto">
                <i data-lucide="plus" class="w-4 h-4 group-hover:rotate-90 transition-all"></i>
                Nuevo Producto
            </a>
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Producto</th>
                            <th class="px-8 py-5 text-left hidden md:table-cell">Categoría / Marca</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Costo</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">P. Minorista</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">P. Mayorista</th>
                            <th class="px-8 py-5 text-left">Estado</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all group">
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $product->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter italic">
                                        {{ $product->internal_code }} @if($product->barcode) · {{ $product->barcode }} @endif
                                    </p>
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-gray-500 dark:text-gray-400 italic hidden md:table-cell">
                                    {{ $product->category->name ?? '—' }} / {{ $product->brand->name ?? '—' }}
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                    ${{ number_format($product->cost, 2) }}
                                </td>
                                <td class="px-8 py-6 text-sm font-black text-gray-700 dark:text-gray-200 italic hidden sm:table-cell">
                                    {{ $product->retail_price !== null ? '$' . number_format($product->retail_price, 2) : '—' }}
                                </td>
                                <td class="px-8 py-6 text-sm font-black text-gray-700 dark:text-gray-200 italic hidden sm:table-cell">
                                    {{ $product->wholesale_price !== null ? '$' . number_format($product->wholesale_price, 2) : '—' }}
                                </td>
                                <td class="px-8 py-6">
                                    @php
                                        $statusColors = [
                                            'active' => 'bg-emerald-500/10 text-emerald-600',
                                            'inactive' => 'bg-gray-400/10 text-gray-500',
                                            'discontinued' => 'bg-red-500/10 text-red-500',
                                        ];
                                        $statusLabels = ['active' => 'Activo', 'inactive' => 'Inactivo', 'discontinued' => 'Discontinuado'];
                                        $statusValue = $product->status?->value ?? 'active';
                                    @endphp
                                    <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full {{ $statusColors[$statusValue] ?? '' }}">
                                        {{ $statusLabels[$statusValue] ?? $statusValue }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('product.edit', $product) }}"
                                            class="p-2.5 bg-gray-50 dark:bg-dark hover:bg-primary hover:text-white transition-all rounded-xl border border-transparent shadow-sm group/btn">
                                            <i data-lucide="edit-3" class="w-4 h-4 transition-transform group-hover/btn:rotate-12"></i>
                                        </a>
                                        <form action="{{ route('product.destroy', $product) }}" method="POST" onsubmit="return confirm('¿Eliminar este producto?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2.5 bg-gray-50 dark:bg-dark hover:bg-red-500 hover:text-white transition-all rounded-xl border border-transparent shadow-sm group/btn">
                                                <i data-lucide="trash-2" class="w-4 h-4 transition-transform group-hover/btn:scale-110"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="package" class="w-16 h-16 mb-4 opacity-20"></i>
                                        <p class="text-lg font-black tracking-tight italic">No hay productos todavía</p>
                                        <p class="text-xs uppercase font-bold mt-1 opacity-50 tracking-widest">Empezá cargando tu primer producto</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-8 bg-gray-50/30 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
                {{ $products->links() }}
            </div>
        </div>
    </div>
@endsection
