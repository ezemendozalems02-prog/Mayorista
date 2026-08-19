@extends('layouts.admin')

@section('title', 'Nueva Compra')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500 pb-20" x-data="{
                                items: [],
                                discount: 0,
                                supplierId: '',
                                products: {{ \Illuminate\Support\Js::from($products) }},

                                costFor(product) {
                                    const bySupplier = product.supplier_costs?.[this.supplierId];
                                    return bySupplier !== undefined ? bySupplier : product.cost;
                                },
                                addProduct(id) {
                                    const product = this.products.find(p => p.id == id);
                                    if (!product) return;
                                    const existing = this.items.find(i => i.id == id);
                                    if (existing) { existing.quantity++; return; }
                                    this.items.push({ ...product, quantity: 1, unit_cost: this.costFor(product) });
                                },
                                addProductByBarcode(code) {
                                    // Este picker no trae barcode por producto; el escaner busca por
                                    // codigo interno como alternativa simple.
                                    const product = this.products.find(p => p.internal_code === code);
                                    if (product) { this.addProduct(product.id); }
                                    else { window.toast('No se encontró ningún producto con ese código.', 'error'); }
                                },
                                removeItem(id) { this.items = this.items.filter(i => i.id != id); },
                                get subtotal() {
                                    return this.items.reduce((acc, item) => acc + (parseFloat(item.unit_cost || 0) * item.quantity), 0);
                                },
                                get total() { return this.subtotal - parseFloat(this.discount || 0); },
                             }" @mito:barcode-scanned.window="addProductByBarcode($event.detail.code)">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="truck" class="w-8 h-8 text-primary"></i> Nueva Compra
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Se carga como pendiente; el stock se suma recién al confirmar la recepción.</p>
            </div>
            <a href="{{ route('purchase.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <form action="{{ route('purchase.store') }}" method="POST" class="space-y-8">
            @csrf

            <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-10 space-y-6">
                <div class="space-y-2">
                    <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Proveedor <span class="text-red-500">*</span></label>
                    <select name="supplier_id" x-model="supplierId" required
                        class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold">
                        <option value="">— Elegí un proveedor —</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->business_name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Escaner -->
            <div class="bg-white dark:bg-dark-alt rounded-[32px] border border-gray-100 dark:border-white/5 shadow-sm p-4 md:p-5">
                <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3 ml-1">Buscar por código interno</p>
                @include('partials.barcode-scanner', ['placeholder' => 'Escaneá o escribí el código interno del producto...', 'autofocus' => false])
            </div>

            <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-10 space-y-6">
                <h2 class="text-xs font-black text-primary uppercase tracking-[0.3em] flex items-center gap-2 italic">
                    <i data-lucide="plus" class="w-4 h-4"></i> Productos
                </h2>

                <div class="relative group" x-data="{ searchOpen: false, search: '' }" @click.away="searchOpen = false">
                    <i data-lucide="search" class="absolute left-6 top-5 w-5 h-5 text-gray-400 z-10 pointer-events-none"></i>
                    <input type="text" x-model="search" @focus="searchOpen = true"
                        placeholder="Buscar producto por nombre o código..."
                        class="w-full pl-16 pr-6 py-5 rounded-3xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-black italic">

                    <div x-show="searchOpen && search.length > 0" x-cloak
                        class="absolute left-0 right-0 z-50 mt-2 bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/10 rounded-3xl shadow-xl max-h-72 overflow-y-auto py-2">
                        <template x-for="product in products.filter(p => `${p.name} ${p.internal_code}`.toLowerCase().includes(search.toLowerCase())).slice(0, 30)" :key="product.id">
                            <div @click="addProduct(product.id); search = ''; searchOpen = false"
                                class="px-6 py-4 hover:bg-primary/5 cursor-pointer border-b border-gray-50 dark:border-white/5 last:border-0 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic" x-text="product.name"></p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1" x-text="product.internal_code"></p>
                                </div>
                                <i data-lucide="plus" class="w-4 h-4 text-primary opacity-0 group-hover:opacity-100"></i>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-8">
                    <template x-if="items.length > 0">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-[10px] text-gray-400 font-black uppercase tracking-widest border-b border-gray-100 dark:border-white/5 italic">
                                        <th class="px-4 py-4 text-left">Producto</th>
                                        <th class="px-4 py-4 text-center">Cant.</th>
                                        <th class="px-4 py-4 text-right">Costo Unit.</th>
                                        <th class="px-4 py-4 text-right">Total</th>
                                        <th class="px-4 py-4 text-right w-10"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                                    <template x-for="(item, index) in items" :key="item.id">
                                        <tr>
                                            <td class="px-4 py-6">
                                                <span class="text-sm font-black text-gray-900 dark:text-gray-100 italic" x-text="item.name"></span>
                                                <input type="hidden" :name="`items[${index}][product_id]`" :value="item.id">
                                                <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
                                                <input type="hidden" :name="`items[${index}][unit_cost]`" :value="item.unit_cost">
                                            </td>
                                            <td class="px-4 py-6 text-center">
                                                <input type="number" min="1" x-model.number="item.quantity"
                                                    class="w-16 px-2 py-1.5 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-black text-center text-sm">
                                            </td>
                                            <td class="px-4 py-6 text-right">
                                                <input type="number" step="0.01" min="0" x-model.number="item.unit_cost"
                                                    class="w-24 px-2 py-1.5 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-black text-right text-sm">
                                            </td>
                                            <td class="px-4 py-6 text-right font-black text-primary italic"
                                                x-text="`$${(parseFloat(item.unit_cost || 0) * item.quantity).toFixed(2)}`"></td>
                                            <td class="px-4 py-6 text-right">
                                                <button type="button" @click="removeItem(item.id)" class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                    <template x-if="items.length == 0">
                        <div class="py-16 text-center border-2 border-dashed border-gray-100 dark:border-white/5 rounded-[40px]">
                            <i data-lucide="package" class="w-12 h-12 text-gray-200 mx-auto mb-4"></i>
                            <p class="text-sm font-bold text-gray-300 uppercase tracking-widest italic">No agregaste productos aún</p>
                        </div>
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-10 space-y-2">
                    <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Notas</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold"></textarea>
                </div>

                <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-8 md:p-10 space-y-6">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Descuento</label>
                        <input type="number" step="0.01" name="discount" x-model="discount"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent outline-none font-black text-primary">
                    </div>
                    <div class="bg-gray-50 dark:bg-dark p-6 rounded-[30px] space-y-3 border border-gray-100 dark:border-white/5">
                        <div class="flex justify-between items-center text-[10px] font-black uppercase text-gray-400 tracking-widest">
                            <span>Subtotal</span><span x-text="`$${subtotal.toFixed(2)}`"></span>
                        </div>
                        <div class="pt-3 border-t border-gray-200 dark:border-white/10 flex justify-between items-center">
                            <span class="text-xs font-black uppercase italic">Total</span>
                            <span class="text-2xl font-black text-primary italic" x-text="`$${total.toFixed(2)}`"></span>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" :disabled="items.length == 0 || !supplierId"
                class="w-full bg-primary hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black py-5 rounded-3xl shadow-2xl shadow-primary/30 active:scale-95 transition-all text-xs uppercase tracking-[0.2em] flex items-center justify-center gap-3 italic">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                Cargar Compra
            </button>
        </form>
    </div>
@endsection
