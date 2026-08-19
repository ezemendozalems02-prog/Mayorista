@extends('layouts.admin')
@section('title', 'Nueva Factura')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 pb-24 animate-in fade-in slide-in-from-bottom-5 duration-500"
    x-data="{
        items: [{ id: 1, descripcion: '', cantidad: 1, precio_unitario: 0 }],
        nextId: 2,
        tipoComprobante: '{{ old('tipo_comprobante', $setting?->tipo_comprobante_default ?? 'B') }}',
        addItem() {
            this.items.push({ id: this.nextId++, descripcion: '', cantidad: 1, precio_unitario: 0 });
        },
        removeItem(id) {
            if (this.items.length > 1) this.items = this.items.filter(i => i.id !== id);
        },
        get subtotal() {
            return this.items.reduce((acc, i) => acc + (parseFloat(i.cantidad) || 0) * (parseFloat(i.precio_unitario) || 0), 0);
        },
        get iva() {
            return this.tipoComprobante !== 'C' ? this.subtotal * 0.21 : 0;
        },
        get total() {
            return this.tipoComprobante !== 'C' ? this.subtotal + this.iva : this.subtotal;
        },
        fmt(n) {
            return '$ ' + parseFloat(n || 0).toLocaleString('es-AR', { minimumFractionDigits: 2 });
        }
    }">

    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('arca.comprobantes') }}" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 text-gray-400 hover:text-gray-600 transition-colors">
            <i data-lucide="chevron-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="file-plus" class="w-8 h-8 text-violet-500"></i>
                Nueva Factura
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-0.5">Creá y emitir un comprobante electrónico</p>
        </div>
    </div>

    @if(!$setting || !$setting->activo)
    <div class="flex items-center gap-3 px-5 py-4 bg-amber-500/10 border border-amber-500/20 rounded-2xl text-sm font-bold text-amber-700 dark:text-amber-300">
        <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i>
        La configuración fiscal no está activa. <a href="{{ route('arca.configuracion') }}" class="underline">Configurar ahora →</a>
    </div>
    @endif

    <form action="{{ route('arca.store-factura') }}" method="POST" class="space-y-6">
        @csrf

        {{-- Receptor --}}
        <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-8">
            <h2 class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4"></i> Datos del Receptor
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="space-y-1.5 sm:col-span-2">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Nombre / Razón Social *</label>
                    <input type="text" name="cliente_nombre" value="{{ old('cliente_nombre') }}" required
                        class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm"
                        placeholder="Consumidor Final">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">CUIT / DNI</label>
                    <input type="text" name="cliente_documento" value="{{ old('cliente_documento') }}"
                        class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm"
                        placeholder="Opcional">
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Condición IVA</label>
                    <select name="cliente_condicion_iva" class="w-full px-5 py-3.5 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none font-bold text-sm">
                        <option value="">— Sin especificar —</option>
                        <option value="CONSUMIDOR_FINAL" {{ old('cliente_condicion_iva') === 'CONSUMIDOR_FINAL' ? 'selected' : '' }}>Consumidor Final</option>
                        <option value="RESPONSABLE_INSCRIPTO" {{ old('cliente_condicion_iva') === 'RESPONSABLE_INSCRIPTO' ? 'selected' : '' }}>Responsable Inscripto</option>
                        <option value="MONOTRIBUTO" {{ old('cliente_condicion_iva') === 'MONOTRIBUTO' ? 'selected' : '' }}>Monotributo</option>
                        <option value="EXENTO" {{ old('cliente_condicion_iva') === 'EXENTO' ? 'selected' : '' }}>Exento</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Tipo Comprobante --}}
        <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-8">
            <h2 class="text-[11px] font-black uppercase tracking-widest text-gray-400 mb-6 flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4"></i> Tipo de Comprobante
            </h2>
            <div class="flex gap-3">
                @foreach(['A' => 'Factura A', 'B' => 'Factura B', 'C' => 'Factura C'] as $tipo => $label)
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="tipo_comprobante" value="{{ $tipo }}" x-model="tipoComprobante" class="sr-only">
                    <div :class="tipoComprobante === '{{ $tipo }}' ? 'bg-violet-600 text-white shadow-xl shadow-violet-500/20' : 'bg-gray-50 dark:bg-dark text-gray-600 dark:text-gray-400 hover:bg-gray-100'"
                        class="text-center py-4 rounded-2xl font-black text-sm transition-all">
                        {{ $label }}
                    </div>
                </label>
                @endforeach
            </div>
            <p class="mt-3 text-xs text-gray-400">
                <span x-show="tipoComprobante === 'A'">Factura A: para Responsables Inscriptos. Discrimina IVA.</span>
                <span x-show="tipoComprobante === 'B'">Factura B: para Consumidores Finales. IVA incluido.</span>
                <span x-show="tipoComprobante === 'C'">Factura C: para Monotributistas y Exentos. Sin IVA discriminado.</span>
            </p>
        </div>

        {{-- Items --}}
        <div class="bg-white dark:bg-dark-alt rounded-3xl border border-gray-100 dark:border-white/5 shadow-sm p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-[11px] font-black uppercase tracking-widest text-gray-400 flex items-center gap-2">
                    <i data-lucide="list" class="w-4 h-4"></i> Productos / Servicios
                </h2>
                <button type="button" @click="addItem(); $nextTick(() => lucide.createIcons())"
                    class="flex items-center gap-1.5 px-4 py-2 bg-violet-500/10 hover:bg-violet-500/20 text-violet-600 dark:text-violet-400 rounded-xl text-xs font-black uppercase tracking-widest transition-colors">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Agregar ítem
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="item in items" :key="item.id">
                    <div class="grid grid-cols-12 gap-3 items-start">
                        <div class="col-span-12 sm:col-span-5 space-y-1">
                            <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Descripción *</label>
                            <input type="text" :name="'items[' + item.id + '][descripcion]'" x-model="item.descripcion" required
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none text-sm font-medium"
                                placeholder="Ej: Reparación de pantalla">
                        </div>
                        <div class="col-span-4 sm:col-span-2 space-y-1">
                            <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Cantidad</label>
                            <input type="number" :name="'items[' + item.id + '][cantidad]'" x-model="item.cantidad" min="0.01" step="0.01" required
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none text-sm font-medium">
                        </div>
                        <div class="col-span-5 sm:col-span-3 space-y-1">
                            <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Precio Unit.</label>
                            <input type="number" :name="'items[' + item.id + '][precio_unitario]'" x-model="item.precio_unitario" min="0" step="0.01" required
                                class="w-full px-4 py-3 rounded-xl bg-gray-50 dark:bg-dark border border-transparent focus:border-violet-500/50 outline-none text-sm font-medium">
                        </div>
                        <div class="col-span-10 sm:col-span-2 space-y-1">
                            <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">Subtotal</label>
                            <div class="px-4 py-3 rounded-xl bg-violet-500/5 text-sm font-black text-violet-600 dark:text-violet-400"
                                x-text="fmt((parseFloat(item.cantidad)||0) * (parseFloat(item.precio_unitario)||0))"></div>
                        </div>
                        <div class="col-span-2 sm:col-span-1 flex items-end pb-0.5">
                            <button type="button" @click="removeItem(item.id)" :disabled="items.length === 1"
                                class="p-3 rounded-xl text-gray-400 hover:text-red-500 hover:bg-red-500/10 transition-colors disabled:opacity-30">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Totales --}}
            <div class="mt-6 pt-5 border-t border-gray-100 dark:border-white/5 flex justify-end">
                <div class="w-full max-w-xs space-y-2 text-sm">
                    <div class="flex justify-between text-gray-500">
                        <span>Subtotal</span>
                        <span x-text="fmt(subtotal)" class="font-bold"></span>
                    </div>
                    <div x-show="tipoComprobante !== 'C'" class="flex justify-between text-gray-500">
                        <span>IVA (21%)</span>
                        <span x-text="fmt(iva)" class="font-bold"></span>
                    </div>
                    <div class="flex justify-between font-black text-base pt-2 border-t border-gray-200 dark:border-white/10">
                        <span>Total</span>
                        <span x-text="fmt(total)" class="text-violet-600 dark:text-violet-400"></span>
                    </div>
                    <p x-show="tipoComprobante === 'C'" class="text-xs text-gray-400">* IVA incluido en el total (no discriminado)</p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row justify-end gap-3">
            <button type="submit" name="autorizar" value="0"
                class="px-8 py-3.5 rounded-2xl font-black text-sm uppercase tracking-widest border border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                Guardar Borrador
            </button>
            <button type="submit" name="autorizar" value="1"
                class="btn-violet px-8 py-3.5 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl flex items-center gap-2">
                <i data-lucide="zap" class="w-4 h-4"></i>
                Emitir con ARCA
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
@endsection
