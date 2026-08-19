@extends('layouts.admin')

@section('title', 'Stock')

@section('content')
    <div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-8 right-8 z-[100] bg-emerald-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <form action="{{ route('stock.index') }}" method="GET" class="flex-1 flex flex-col md:flex-row items-center gap-4 w-full">
                <div class="relative w-full md:w-80 group">
                    <i data-lucide="search"
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                    <input name="search" value="{{ request('search') }}" type="text" placeholder="Nombre, código o código de barras..."
                        class="w-full pl-11 pr-4 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none focus:border-primary transition-all shadow-sm text-sm font-medium">
                </div>

                <div class="flex items-center bg-white dark:bg-dark-alt p-1.5 rounded-2xl border border-gray-100 dark:border-white/5 shadow-sm">
                    <a href="{{ route('stock.index') }}"
                        class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ !request('low_stock') ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-400 hover:text-gray-600' }}">
                        Todos </a>
                    <a href="{{ route('stock.index', array_merge(request()->query(), ['low_stock' => 1])) }}"
                        class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ request('low_stock') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-gray-400 hover:text-gray-600' }}">
                        Stock Bajo </a>
                </div>
            </form>
        </div>

        <!-- Escaner (Fase 7): escanear un producto salta directo a su historial de movimientos -->
        <div x-data="{}" @mito:barcode-scanned.window="window.location.href = '{{ route('stock.find-by-barcode') }}?barcode=' + encodeURIComponent($event.detail.code)"
            class="bg-white dark:bg-dark-alt rounded-[32px] border border-gray-100 dark:border-white/5 shadow-sm p-4 md:p-5">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3 ml-1">Escanear Producto</p>
            @include('partials.barcode-scanner', ['placeholder' => 'Escaneá un código para ir directo a sus movimientos...', 'autofocus' => false])
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Producto</th>
                            <th class="px-8 py-5 text-left hidden md:table-cell">Categoría / Marca</th>
                            <th class="px-8 py-5 text-left">Stock Actual</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Stock Mínimo</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($products as $product)
                            @php
                                $currentStock = $product->stock?->quantity ?? 0;
                                $isLow = $product->min_stock > 0 && $currentStock <= $product->min_stock;
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all group">
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $product->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter italic">{{ $product->internal_code }}</p>
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-gray-500 dark:text-gray-400 italic hidden md:table-cell">
                                    {{ $product->category->name ?? '—' }} / {{ $product->brand->name ?? '—' }}
                                </td>
                                <td class="px-8 py-6">
                                    <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full {{ $isLow ? 'bg-red-500/10 text-red-500' : 'bg-emerald-500/10 text-emerald-600' }}">
                                        {{ $currentStock }} unidades
                                    </span>
                                    @if($isLow)
                                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-red-500 inline-block ml-1"></i>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                    {{ $product->min_stock }}
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <a href="{{ route('stock.movements', $product) }}"
                                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-50 dark:bg-dark hover:bg-primary hover:text-white transition-all rounded-xl border border-transparent shadow-sm text-xs font-black uppercase tracking-widest">
                                        <i data-lucide="history" class="w-4 h-4"></i>
                                        Movimientos
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="boxes" class="w-16 h-16 mb-4 opacity-20"></i>
                                        <p class="text-lg font-black tracking-tight italic">No hay productos</p>
                                        <p class="text-xs uppercase font-bold mt-1 opacity-50 tracking-widest">Cargá productos en el catálogo primero</p>
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
