@extends('layouts.public')

@section('title', 'Catálogo — ' . $organization->name)

@section('content')
    <div class="max-w-6xl mx-auto px-4 md:px-6 py-10 md:py-14 space-y-10">

        <!-- Header -->
        <header class="text-center space-y-3">
            <h1 class="text-3xl md:text-4xl font-black italic tracking-tight text-gray-900">{{ $organization->name }}</h1>
            <p class="text-sm text-gray-500 font-medium">Catálogo de productos y precios</p>
        </header>

        <!-- Search + Category Filter -->
        <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
            <form action="{{ route('catalog.public') }}" method="GET" class="relative w-full md:w-96">
                @if(request('category_id'))
                    <input type="hidden" name="category_id" value="{{ request('category_id') }}">
                @endif
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input name="search" value="{{ request('search') }}" type="text" placeholder="Buscar producto o código..."
                    class="w-full pl-11 pr-4 py-3 rounded-2xl bg-white border border-gray-100 outline-none focus:border-primary transition-all shadow-sm text-sm font-medium">
            </form>

            @if($categories->isNotEmpty())
                <div class="flex items-center gap-2 overflow-x-auto pb-1 w-full md:w-auto scrollbar-hide">
                    <a href="{{ route('catalog.public', ['search' => request('search')]) }}"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest whitespace-nowrap transition-all {{ !request('category_id') ? 'bg-primary text-white shadow-lg' : 'bg-white text-gray-400 border border-gray-100' }}">
                        Todas
                    </a>
                    @foreach($categories as $category)
                        <a href="{{ route('catalog.public', ['category_id' => $category->id, 'search' => request('search')]) }}"
                            class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest whitespace-nowrap transition-all {{ (int) request('category_id') === $category->id ? 'bg-primary text-white shadow-lg' : 'bg-white text-gray-400 border border-gray-100' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Product Grid -->
        @if($products->isEmpty())
            <div class="py-24 text-center">
                <i data-lucide="package-x" class="w-16 h-16 mx-auto mb-4 text-gray-200"></i>
                <p class="text-lg font-black tracking-tight italic text-gray-400">No encontramos productos</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                @foreach($products as $product)
                    @php $inStock = $product->current_stock > 0; @endphp
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-5 flex flex-col gap-3 hover:shadow-xl transition-all">
                        <div class="flex items-start justify-between gap-2">
                            @if($product->category)
                                <span class="text-[9px] font-black uppercase tracking-widest text-primary bg-primary/10 px-2.5 py-1 rounded-full">{{ $product->category->name }}</span>
                            @else
                                <span></span>
                            @endif
                            <span class="text-[9px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full {{ $inStock ? 'bg-emerald-500/10 text-emerald-600' : 'bg-gray-400/10 text-gray-400' }}">
                                {{ $inStock ? 'Disponible' : 'Sin stock' }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-black text-gray-900 italic tracking-tight leading-snug">{{ $product->name }}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter mt-1">{{ $product->internal_code }}{{ $product->brand ? ' · ' . $product->brand->name : '' }}</p>
                        </div>
                        <p class="text-xl font-black text-primary italic tracking-tighter">${{ number_format($product->retail_price ?? 0, 2, ',', '.') }}</p>
                    </div>
                @endforeach
            </div>

            <div>
                {{ $products->links() }}
            </div>
        @endif

        <footer class="text-center pt-6 border-t border-gray-100">
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Precios sujetos a cambio sin previo aviso · Consultá por precios mayoristas</p>
        </footer>
    </div>
@endsection
