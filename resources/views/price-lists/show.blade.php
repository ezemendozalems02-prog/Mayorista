@extends('layouts.admin')

@section('title', $priceList->name . ' — Precios')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-8 right-8 z-[100] bg-emerald-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="tags" class="w-8 h-8 text-primary"></i> {{ $priceList->name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    {{ $priceList->items->count() }} producto(s) con precio especial. Los demás usan su precio base (minorista/mayorista) del catálogo.
                </p>
            </div>
            <a href="{{ route('price-list.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <!-- Escaner (Fase 7): busca el producto por codigo de barras (recarga con ese filtro) -->
        <div x-data="{}" @mito:barcode-scanned.window="window.location.href = '{{ route('price-list.show', $priceList) }}?search=' + encodeURIComponent($event.detail.code)"
            class="bg-white dark:bg-dark-alt rounded-[32px] border border-gray-100 dark:border-white/5 shadow-sm p-4 md:p-5">
            <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3 ml-1">Escanear producto</p>
            @include('partials.barcode-scanner', ['placeholder' => 'Escaneá un producto para cargarle un precio especial...', 'autofocus' => false])
        </div>

        <!-- Search -->
        <form action="{{ route('price-list.show', $priceList) }}" method="GET" class="relative w-full group">
            <i data-lucide="search"
                class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
            <input name="search" value="{{ request('search') }}" type="text" placeholder="Buscar producto por nombre o código..."
                class="w-full pl-11 pr-4 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none focus:border-primary transition-all shadow-sm text-sm font-medium">
        </form>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Producto</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Precio Base</th>
                            <th class="px-8 py-5 text-left">Precio en esta Lista</th>
                            <th class="px-8 py-5 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($products as $product)
                            @php $item = $itemsByProduct->get($product->id); @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all">
                                <td class="px-8 py-5">
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $product->name }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter italic">{{ $product->internal_code }}</p>
                                </td>
                                <td class="px-8 py-5 text-xs font-bold text-gray-500 dark:text-gray-400 hidden sm:table-cell">
                                    {{ $product->retail_price !== null ? '$' . number_format($product->retail_price, 2) : '—' }}
                                    @if($product->wholesale_price !== null)
                                        <span class="block text-[10px] opacity-70">May: ${{ number_format($product->wholesale_price, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-8 py-5">
                                    <form action="{{ route('price-list.items.set', $priceList) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="number" step="0.01" min="0" name="price" value="{{ $item?->price }}" placeholder="Precio base"
                                            class="w-28 px-3 py-2 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-black text-sm">
                                        <button type="submit"
                                            class="p-2 bg-gray-50 dark:bg-dark hover:bg-primary hover:text-white transition-all rounded-xl border border-transparent shadow-sm">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    @if($item)
                                        <span class="text-[10px] font-black uppercase bg-primary/10 text-primary px-3 py-1 rounded-full">Precio especial</span>
                                    @else
                                        <span class="text-[10px] font-black uppercase text-gray-300">Precio base</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-16 text-center text-gray-400">
                                    <p class="text-sm font-black tracking-tight italic">No se encontraron productos</p>
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
