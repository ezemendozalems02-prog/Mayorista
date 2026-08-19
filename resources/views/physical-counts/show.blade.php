@extends('layouts.admin')

@section('title', 'Conteo Físico ' . $physicalCount->code)

@section('content')
    @php
        $statusColors = ['open' => 'bg-amber-500/10 text-amber-600', 'completed' => 'bg-emerald-500/10 text-emerald-600', 'cancelled' => 'bg-gray-400/10 text-gray-500'];
        $statusLabels = ['open' => 'En Curso', 'completed' => 'Finalizado', 'cancelled' => 'Cancelado'];
        $statusValue = $physicalCount->status?->value ?? 'open';
        $isOpen = $statusValue === 'open';
    @endphp

    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500">

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
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

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="clipboard-list" class="w-8 h-8 text-primary"></i> {{ $physicalCount->code }}
                    <span class="text-[10px] font-black uppercase px-3 py-1 rounded-full {{ $statusColors[$statusValue] ?? '' }}">
                        {{ $statusLabels[$statusValue] ?? $statusValue }}
                    </span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    {{ $physicalCount->category->name ?? 'Todas las categorías' }} ·
                    Iniciado por {{ $physicalCount->creator->name ?? '—' }} el {{ $physicalCount->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <a href="{{ route('physical-count.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        @if($isOpen)
            <!-- Escaner (Fase 7): busca el producto en la lista de este conteo y enfoca su input -->
            <div x-data="{
                    items: {{ \Illuminate\Support\Js::from($physicalCount->items->map(fn ($i) => ['product_id' => $i->product_id, 'barcode' => $i->product->barcode])) }},
                }"
                @mito:barcode-scanned.window="
                    const item = items.find(i => i.barcode && i.barcode === $event.detail.code);
                    if (item) {
                        const el = document.getElementById('count-input-' + item.product_id);
                        if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); el.focus(); el.select(); }
                    } else {
                        window.toast('Ese código no está en este conteo.', 'error');
                    }
                "
                class="bg-white dark:bg-dark-alt rounded-[32px] border border-gray-100 dark:border-white/5 shadow-sm p-4 md:p-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3 ml-1">Escanear para buscar en este conteo</p>
                @include('partials.barcode-scanner', ['placeholder' => 'Escaneá un producto de este conteo...', 'autofocus' => true])
            </div>
        @endif

        <!-- Items -->
        <form action="{{ route('physical-count.save', $physicalCount) }}" method="POST" class="space-y-6">
            @csrf
            <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                                <th class="px-8 py-5 text-left">Producto</th>
                                <th class="px-8 py-5 text-left">Sistema</th>
                                <th class="px-8 py-5 text-left">Contado</th>
                                <th class="px-8 py-5 text-left hidden sm:table-cell">Diferencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                            @forelse($physicalCount->items as $item)
                                @php $diff = $item->counted_quantity === null ? null : $item->counted_quantity - $item->expected_quantity; @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all">
                                    <td class="px-8 py-5">
                                        <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $item->product->name }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter italic">{{ $item->product->internal_code }}</p>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-bold text-gray-500 dark:text-gray-400">
                                        {{ $item->expected_quantity }}
                                    </td>
                                    <td class="px-8 py-5">
                                        @if($isOpen)
                                            <input type="number" min="0" id="count-input-{{ $item->product_id }}"
                                                name="counts[{{ $item->id }}]" value="{{ $item->counted_quantity }}"
                                                placeholder="—"
                                                class="w-24 px-3 py-2 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-black text-sm">
                                        @else
                                            <span class="text-sm font-black text-gray-700 dark:text-gray-200">{{ $item->counted_quantity ?? '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-5 hidden sm:table-cell">
                                        @if($diff === null)
                                            <span class="text-xs text-gray-400 italic">Sin contar</span>
                                        @elseif($diff === 0)
                                            <span class="text-xs font-black text-emerald-600">OK</span>
                                        @else
                                            <span class="text-xs font-black {{ $diff > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                                {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-16 text-center text-gray-400">
                                        <p class="text-sm font-black tracking-tight italic">No hay productos en este conteo</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($isOpen)
                <div class="flex flex-col md:flex-row gap-4">
                    <button type="submit"
                        class="flex-1 bg-white dark:bg-dark-alt hover:bg-gray-50 dark:hover:bg-white/5 text-gray-700 dark:text-gray-200 font-black py-4 rounded-2xl shadow-lg border border-gray-100 dark:border-white/5 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2">
                        <i data-lucide="save" class="w-5 h-5"></i>
                        Guardar Cantidades
                    </button>
                </div>
            @endif
        </form>

        @if($isOpen)
            <div class="flex flex-col md:flex-row gap-4">
                <form action="{{ route('physical-count.finalize', $physicalCount) }}" method="POST" class="flex-1"
                    onsubmit="return confirm('¿Finalizar el conteo? Se van a generar ajustes de stock para las diferencias encontradas. Esta acción no se puede deshacer.');">
                    @csrf
                    <button type="submit"
                        class="w-full bg-primary hover:bg-primary/90 text-white font-black py-4 rounded-2xl shadow-xl shadow-primary/25 active:scale-95 transition-all text-sm uppercase tracking-widest flex items-center justify-center gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5"></i>
                        Finalizar Conteo
                    </button>
                </form>
                <form action="{{ route('physical-count.cancel', $physicalCount) }}" method="POST"
                    onsubmit="return confirm('¿Cancelar este conteo? No se va a generar ningún ajuste de stock.');">
                    @csrf
                    <button type="submit"
                        class="px-8 bg-gray-100 dark:bg-dark text-gray-500 font-black py-4 rounded-2xl hover:bg-red-500 hover:text-white transition-all text-sm uppercase tracking-widest">
                        Cancelar Conteo
                    </button>
                </form>
            </div>
        @endif
    </div>
@endsection
