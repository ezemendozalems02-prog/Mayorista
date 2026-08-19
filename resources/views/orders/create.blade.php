@extends('layouts.admin')

@section('title', 'Nuevo Pedido')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500 pb-20" x-data="{
                                items: [],
                                discount: 0,
                                products: {{ \Illuminate\Support\Js::from($products) }},
                                clients: {{ \Illuminate\Support\Js::from($clients->map(fn ($c) => ['id' => $c->id, 'name' => $c->display_name, 'client_type' => $c->client_type?->value])) }},
                                selectedClientId: '',
                                showQuickClientModal: false,

                                get selectedClient() {
                                    return this.clients.find(c => c.id == this.selectedClientId) || null;
                                },
                                defaultPrice(product) {
                                    return this.selectedClient?.client_type === 'wholesale' ? product.wholesale_price : product.retail_price;
                                },
                                addProduct(id) {
                                    const product = this.products.find(p => p.id == id);
                                    if (!product) return;
                                    const existing = this.items.find(i => i.id == id);
                                    if (existing) { existing.quantity++; return; }
                                    this.items.push({ ...product, quantity: 1, unit_price: this.defaultPrice(product) });
                                    lucide.createIcons();
                                },
                                addProductByBarcode(code) {
                                    const product = this.products.find(p => p.barcode && p.barcode === code);
                                    if (product) {
                                        this.addProduct(product.id);
                                    } else {
                                        window.toast('No se encontró ningún producto con ese código.', 'error');
                                    }
                                },
                                removeItem(id) {
                                    this.items = this.items.filter(i => i.id != id);
                                },
                                plusQty(id) {
                                    const item = this.items.find(i => i.id == id);
                                    if (item) item.quantity++;
                                },
                                minusQty(id) {
                                    const item = this.items.find(i => i.id == id);
                                    if (item && item.quantity > 1) item.quantity--;
                                },
                                get subtotal() {
                                    return this.items.reduce((acc, item) => acc + (parseFloat(item.unit_price || 0) * item.quantity), 0);
                                },
                                get total() {
                                    return this.subtotal - parseFloat(this.discount || 0);
                                }
                             }" @close-quick-client-order.window="showQuickClientModal = false"
        @mito:barcode-scanned.window="addProductByBarcode($event.detail.code)">

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                class="fixed bottom-8 right-8 z-[100] bg-red-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic max-w-md">
                <i data-lucide="alert-triangle" class="w-5 h-5 flex-shrink-0"></i>
                {{ session('error') }}
            </div>
        @endif

        <!-- Quick Client Modal -->
        <div x-show="showQuickClientModal" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-dark/60 backdrop-blur-sm animate-in fade-in duration-300">
            <div @click.away="showQuickClientModal = false"
                class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl w-full max-w-md p-10 space-y-8 animate-in zoom-in duration-300">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-black italic tracking-tight">Alta Rápida de Cliente</h3>
                    <button @click="showQuickClientModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <form id="order-quick-client-form" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nombre
                            y apellido</label>
                        <input type="text" name="full_name" required
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold"
                            placeholder="Escribí el nombre...">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Email</label>
                        <input type="email" name="email" required
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold"
                            placeholder="cliente@email.com">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Teléfono</label>
                        <input type="text" name="phone"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-bold"
                            placeholder="Ej: +54 9 11 ...">
                    </div>
                    <button type="submit"
                    class="w-full bg-primary text-white font-black py-4 rounded-2xl shadow-xl hover:bg-primary/90 transition-all uppercase text-xs tracking-widest italic">
                        Guardar cliente y continuar
                    </button>
                </form>
            </div>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="clipboard-list" class="w-8 h-8 text-primary"></i> Nuevo Pedido
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">Cargá lo que el cliente encargó. Todavía no se descuenta stock ni se cobra.</p>
            </div>
            <a href="{{ route('order.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <form action="{{ route('order.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Side: Item Picker & List -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Escaner -->
                    <div class="bg-white dark:bg-dark-alt rounded-[32px] border border-gray-100 dark:border-white/5 shadow-sm p-4 md:p-5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 mb-3 ml-1">Escanear producto</p>
                        @include('partials.barcode-scanner', ['placeholder' => 'Escaneá un producto para agregarlo al pedido...', 'autofocus' => false])
                    </div>

                    <!-- Item Selector -->
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-primary/5 dark:shadow-none p-8 md:p-10 space-y-6">
                        <h2
                            class="text-xs font-black text-primary uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="plus" class="w-4 h-4"></i> Selección de Productos
                        </h2>

                        <div class="relative group" x-data="{ searchOpen: false, search: '' }"
                            @click.away="searchOpen = false">
                            <i data-lucide="search"
                                class="absolute left-6 top-5 w-5 h-5 text-gray-400 group-focus-within:text-primary transition-colors z-10 pointer-events-none"></i>
                            <input type="text" x-model="search" @focus="searchOpen = true"
                                placeholder="Buscar producto por nombre o código..."
                                class="w-full pl-16 pr-6 py-5 rounded-3xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-black italic relative z-0">

                            <!-- Searchable Dropdown -->
                            <div x-show="searchOpen && search.length > 0" x-transition.opacity x-cloak
                                class="absolute left-0 right-0 z-50 mt-2 bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/10 rounded-3xl shadow-xl shadow-primary/10 max-h-72 overflow-y-auto overflow-x-hidden scrollbar-hide py-2">
                                <template
                                    x-for="product in products.filter(p => `${p.name} ${p.internal_code} ${p.barcode}`.toLowerCase().includes(search.toLowerCase())).slice(0, 30)"
                                    :key="product.id">
                                    <div @click="addProduct(product.id); search = ''; searchOpen = false"
                                        class="px-6 py-4 hover:bg-primary/5 dark:hover:bg-primary/10 cursor-pointer border-b border-gray-50 dark:border-white/5 last:border-0 transition-all group flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic group-hover:text-primary transition-colors"
                                                x-text="product.name"></p>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                                                <span class="text-primary" x-text="`$${defaultPrice(product).toFixed(2)}`"></span> •
                                                <span x-text="`${product.stock} en stock hoy`"></span>
                                            </p>
                                        </div>
                                        <div
                                            class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i data-lucide="plus" class="w-4 h-4 text-primary"></i>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="products.filter(p => `${p.name} ${p.internal_code} ${p.barcode}`.toLowerCase().includes(search.toLowerCase())).length === 0"
                                    class="px-6 py-8 flex flex-col items-center justify-center text-gray-400">
                                    <i data-lucide="search-x" class="w-8 h-8 mb-2 opacity-50"></i>
                                    <span class="text-xs font-black uppercase tracking-widest italic">Sin resultados</span>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Items Table -->
                        <div class="mt-8">
                            <template x-if="items.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr
                                                class="text-[10px] text-gray-400 font-black uppercase tracking-widest border-b border-gray-100 dark:border-white/5 italic">
                                                <th class="px-4 py-4 text-left">Producto</th>
                                                <th class="px-4 py-4 text-center">Cant.</th>
                                                <th class="px-4 py-4 text-right">Precio Unit.</th>
                                                <th class="px-4 py-4 text-right">Total</th>
                                                <th class="px-4 py-4 text-right w-10"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                                            <template x-for="(item, index) in items" :key="item.id">
                                                <tr class="group animate-in fade-in slide-in-from-left-2 duration-300">
                                                    <td class="px-4 py-6">
                                                        <div class="flex flex-col">
                                                            <span
                                                                class="text-sm font-black text-gray-900 dark:text-gray-100 italic"
                                                                x-text="item.name"></span>
                                                            <span
                                                                class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter"
                                                                x-text="item.internal_code"></span>
                                                            <input type="hidden" :name="`items[${index}][product_id]`" :value="item.id">
                                                            <input type="hidden" :name="`items[${index}][quantity]`" :value="item.quantity">
                                                            <input type="hidden" :name="`items[${index}][unit_price]`" :value="item.unit_price">
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-6 text-center">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <button type="button" @click="minusQty(item.id)"
                                                                class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-500 hover:bg-primary hover:text-white transition-all">
                                                                <i data-lucide="minus" class="w-3 h-3"></i>
                                                            </button>
                                                            <span class="text-sm font-black w-6"
                                                                x-text="item.quantity"></span>
                                                            <button type="button" @click="plusQty(item.id)"
                                                                class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-500 hover:bg-primary hover:text-white transition-all">
                                                                <i data-lucide="plus" class="w-3 h-3"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-6 text-right">
                                                        <input type="number" step="0.01" min="0" x-model.number="item.unit_price"
                                                            class="w-24 px-2 py-1.5 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none font-black text-right text-sm">
                                                    </td>
                                                    <td class="px-4 py-6 text-right font-black text-primary italic"
                                                        x-text="`$${(parseFloat(item.unit_price || 0) * item.quantity).toFixed(2)}`">
                                                    </td>
                                                    <td class="px-4 py-6 text-right">
                                                        <button type="button" @click="removeItem(item.id)"
                                                            class="p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all">
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
                                <div
                                    class="py-16 text-center border-2 border-dashed border-gray-100 dark:border-white/5 rounded-[40px]">
                                    <i data-lucide="package" class="w-12 h-12 text-gray-200 mx-auto mb-4"></i>
                                    <p class="text-sm font-bold text-gray-300 uppercase tracking-widest italic">No
                                        seleccionaste productos aún</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-primary/5 dark:shadow-none p-8 md:p-10">
                        <h2
                            class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] flex items-center gap-2 italic mb-6">
                            <i data-lucide="file-text" class="w-4 h-4"></i> Observaciones del Pedido
                        </h2>
                        <textarea name="notes" rows="3"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-primary/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="Escribí aquí cualquier detalle extra..."></textarea>
                    </div>
                </div>

                <!-- Right Side: Totals & Summary -->
                <div class="space-y-8">

                    <!-- Order Settings -->
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-primary/5 dark:shadow-none p-8 md:p-10 space-y-6">
                        <h2
                            class="text-xs font-black text-primary uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="settings" class="w-4 h-4"></i> Datos del Pedido
                        </h2>

                        <div class="space-y-6">
                            <!-- Client -->
                            <div class="space-y-2">
                                <div class="flex justify-between items-center ml-1">
                                    <label
                                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest italic">Cliente</label>
                                    <button type="button" @click="showQuickClientModal = true"
                                        class="text-[10px] font-black text-primary uppercase hover:underline">Nuevo
                                        Cliente</button>
                                </div>
                                <select id="order-client-select" name="client_id" x-model="selectedClientId" required data-placeholder="Buscar cliente..."
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent outline-none font-bold appearance-none italic">
                                    <option value="" disabled>Elegí un cliente...</option>
                                    @foreach($clients as $cli)
                                        <option value="{{ $cli->id }}">{{ $cli->display_name }}{{ $cli->client_type?->value === 'wholesale' ? ' (Mayorista)' : '' }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[10px] text-gray-400 italic ml-1">Un pedido siempre necesita un cliente (a diferencia de una venta de mostrador).</p>
                            </div>

                            <!-- Discount -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Descuento
                                    Total</label>
                                <input type="number" step="0.01" name="discount" x-model="discount"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent font-black italic outline-none text-primary"
                                    placeholder="0.00">
                            </div>

                            <!-- Summary Block -->
                            <div
                                class="bg-gray-50 dark:bg-dark p-6 rounded-[30px] space-y-3 border border-gray-100 dark:border-white/5">
                                <div
                                    class="flex justify-between items-center text-[10px] font-black uppercase text-gray-400 tracking-widest">
                                    <span>Subtotal</span>
                                    <span x-text="`$${subtotal.toFixed(2)}`"></span>
                                </div>
                                <div
                                    class="flex justify-between items-center text-[10px] font-black uppercase text-gray-400 tracking-widest">
                                    <span>Descuento</span>
                                    <span x-text="`- $${parseFloat(discount || 0).toFixed(2)}`"></span>
                                </div>
                                <div
                                    class="pt-3 border-t border-gray-200 dark:border-white/10 flex justify-between items-center">
                                    <span class="text-xs font-black uppercase italic text-gray-900 dark:text-gray-100">Total
                                        Estimado</span>
                                    <div class="text-2xl font-black text-primary italic tracking-tighter"
                                        x-text="`$${total.toFixed(2)}`"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="space-y-4">
                        <button type="submit" :disabled="items.length == 0 || !selectedClientId"
                            class="w-full bg-primary hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black py-5 rounded-3xl shadow-2xl shadow-primary/30 active:scale-95 transition-all text-xs uppercase tracking-[0.2em] flex items-center justify-center gap-3 italic">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                            Guardar Pedido
                        </button>
                        <p class="text-[10px] text-center font-bold text-gray-400 italic">No descuenta stock ni cobra
                            todavía — eso pasa al facturarlo.</p>
                    </div>
                </div>

            </div>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const quickClientForm = document.getElementById('order-quick-client-form');
            const clientSelect = document.getElementById('order-client-select');
            if (!quickClientForm || !clientSelect) return;

            quickClientForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                const formData = new FormData(quickClientForm);
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    const response = await fetch("{{ route('client.quick-store') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    if (!response.ok) {
                        throw new Error('No se pudo crear el cliente.');
                    }

                    const client = await response.json();
                    const optionLabel = client.full_name;
                    const tom = clientSelect.tomselect;

                    if (tom) {
                        tom.addOption({ value: client.id, text: optionLabel });
                        tom.setValue(String(client.id));
                    } else {
                        const newOption = new Option(optionLabel, client.id, true, true);
                        clientSelect.add(newOption);
                    }

                    quickClientForm.reset();
                    window.dispatchEvent(new CustomEvent('close-quick-client-order'));
                } catch (error) {
                    alert('No se pudo crear el cliente. Revisá los datos e intentalo nuevamente.');
                }
            });
        });
    </script>
@endsection
