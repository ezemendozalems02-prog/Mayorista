@extends('layouts.admin')

@section('title', 'Nueva Venta')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500 pb-20" x-data="{ 
                                items: [],
                                discount: 0,
                                exchangeRate: 1,
                                currency: 'USD',
                                inventory: {{ $inventoryItems->toJson() }},
                                showQuickClientModal: false,

                                get groupedInventory() {
                                    const groups = {};
                                    this.inventory.forEach(item => {
                                        const key = `${item.brand}-${item.model}-${item.storage}-${item.color}-${item.cosmetic_condition}-${item.sale_price}`;
                                        if(!groups[key]) {
                                            groups[key] = {
                                                id: key,
                                                brand: item.brand,
                                                model: item.model,
                                                storage: item.storage,
                                                color: item.color || '-',
                                                cosmetic_condition: item.cosmetic_condition,
                                                sale_price: item.sale_price,
                                                stock_available: 0,
                                                item_ids: []
                                            };
                                        }
                                        groups[key].stock_available++;
                                        groups[key].item_ids.push(item.id);
                                    });
                                    return Object.values(groups);
                                },
                                addItem(id) {
                                    const product = this.groupedInventory.find(p => p.id == id);
                                    if (product && !this.items.find(i => i.id == id)) {
                                        this.items.push({...product, quantity: 1, imei: null});
                                        lucide.createIcons();
                                    }
                                },
                                removeItem(id) {
                                    this.items = this.items.filter(i => i.id != id);
                                },
                                plusQty(id) {
                                    const item = this.items.find(i => i.id == id);
                                    if(item && item.quantity < item.stock_available) item.quantity++;
                                },
                                minusQty(id) {
                                    const item = this.items.find(i => i.id == id);
                                    if(item && item.quantity > 1) item.quantity--;
                                },
                                get subtotal() {
                                    return this.items.reduce((acc, item) => acc + (parseFloat(item.sale_price) * item.quantity), 0);
                                },
                                get total() {
                                    return this.subtotal - parseFloat(this.discount || 0);
                                }
                             }" @close-quick-client-sale.window="showQuickClientModal = false">

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
                <form id="sale-quick-client-form" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nombre
                            y apellido</label>
                        <input type="text" name="full_name" required
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-emerald-500/50 outline-none font-bold"
                            placeholder="Escribe el nombre...">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Email</label>
                        <input type="email" name="email" required
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-emerald-500/50 outline-none font-bold"
                            placeholder="cliente@email.com">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Teléfono</label>
                        <input type="text" name="phone"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-emerald-500/50 outline-none font-bold"
                            placeholder="Ej: +54 9 11 ...">
                    </div>
                    <button type="submit"
                    class="w-full bg-emerald-600 text-white font-black py-4 rounded-2xl shadow-xl hover:bg-emerald-700 transition-all uppercase text-xs tracking-widest italic">
                        Guardar cliente y continuar
                    </button>
                </form>
            </div>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="shopping-cart" class="w-8 h-8 text-emerald-500"></i> Nueva Operación
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">Seleccioná los equipos,
                    aplicá descuentos y realizá el cobro.</p>
            </div>
            <a href="{{ route('sale.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <form action="{{ route('sale.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Side: Item Picker & List -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Item Selector -->
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-emerald-500/5 dark:shadow-none p-8 md:p-10 space-y-6">
                        <h2
                            class="text-xs font-black text-emerald-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="plus" class="w-4 h-4"></i> Selección de Artículos
                        </h2>

                        <div class="relative group" x-data="{ searchOpen: false, search: '' }"
                            @click.away="searchOpen = false">
                            <i data-lucide="search"
                                class="absolute left-6 top-5 w-5 h-5 text-gray-400 group-focus-within:text-emerald-500 transition-colors z-10 pointer-events-none"></i>
                            <input type="text" x-model="search" @focus="searchOpen = true"
                                placeholder="Buscar equipo en stock (Ej: iPhone 14 Pro)..."
                                class="w-full pl-16 pr-6 py-5 rounded-3xl bg-gray-50 dark:bg-dark border border-transparent focus:border-emerald-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-black italic relative z-0">

                            <!-- Searchable Dropdown -->
                            <div x-show="searchOpen && search.length > 0" x-transition.opacity x-cloak
                                class="absolute left-0 right-0 z-50 mt-2 bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/10 rounded-3xl shadow-xl shadow-emerald-500/10 max-h-72 overflow-y-auto overflow-x-hidden scrollbar-hide py-2">
                                <template
                                    x-for="item in groupedInventory.filter(i => `${i.brand} ${i.model} ${i.storage} ${i.color} ${i.sale_price}`.toLowerCase().includes(search.toLowerCase()))"
                                    :key="item.id">
                                    <div @click="addItem(item.id); search = ''; searchOpen = false"
                                        class="px-6 py-4 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 cursor-pointer border-b border-gray-50 dark:border-white/5 last:border-0 transition-all group flex items-center justify-between">
                                        <div>
                                            <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors"
                                                x-text="`${item.brand} ${item.model} (${item.storage} | ${item.color})`">
                                            </p>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">
                                                <span class="text-emerald-500" x-text="`$${item.sale_price}`"></span> •
                                                <span x-text="`${item.stock_available} DISPONIBLES`"></span>
                                            </p>
                                        </div>
                                        <div
                                            class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i data-lucide="plus" class="w-4 h-4 text-emerald-500"></i>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="groupedInventory.filter(i => `${i.brand} ${i.model} ${i.storage} ${i.color} ${i.sale_price}`.toLowerCase().includes(search.toLowerCase())).length === 0"
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
                                                <th class="px-4 py-4 text-left">Detalle</th>
                                                <th class="px-4 py-4 text-center">Cant.</th>
                                                <th class="px-4 py-4 text-right">Monto</th>
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
                                                                x-text="`${item.brand} ${item.model}`"></span>
                                                            <span
                                                                class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter"
                                                                x-text="`${item.storage} | ${item.color} | ${item.cosmetic_condition}`"></span>
                                                            <input type="hidden" :name="`items[${index}][item_ids]`"
                                                                :value="JSON.stringify(item.item_ids)">
                                                            <input type="hidden" :name="`items[${index}][quantity]`"
                                                                :value="item.quantity">
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-6 text-center">
                                                        <div class="flex items-center justify-center gap-2">
                                                            <button type="button" @click="minusQty(item.id)"
                                                                x-show="!item.imei"
                                                                class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-500 hover:bg-emerald-500 hover:text-white transition-all">
                                                                <i data-lucide="minus" class="w-3 h-3"></i>
                                                            </button>
                                                            <span class="text-sm font-black w-6"
                                                                x-text="item.quantity"></span>
                                                            <button type="button" @click="plusQty(item.id)"
                                                                x-show="!item.imei"
                                                                class="w-6 h-6 rounded-lg bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-500 hover:bg-emerald-500 hover:text-white transition-all">
                                                                <i data-lucide="plus" class="w-3 h-3"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <td class="px-4 py-6 text-right font-black text-emerald-600 italic"
                                                        x-text="`${currency} ${(parseFloat(item.sale_price) * item.quantity).toFixed(2)}`">
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
                                        seleccionaste artículos aún</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Trade-In (Canje) -->
                    <div x-data="{ hasTradeIn: false }">
                        <div
                            class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-indigo-500/5 dark:shadow-none p-8 md:p-10 space-y-6">
                            <div class="flex items-center justify-between">
                                <h2
                                    class="text-xs font-black text-indigo-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i> Equipo en parte de pago
                                </h2>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="hasTradeIn" name="has_trade_in" value="1"
                                        class="sr-only peer">
                                    <div
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-dark peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-indigo-500">
                                    </div>
                                </label>
                            </div>

                            <div x-show="hasTradeIn" x-collapse x-cloak
                                class="space-y-6 pt-4 animate-in fade-in zoom-in duration-300">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Modelo
                                            / Marca</label>
                                        <input type="text" name="trade_in_model"
                                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 outline-none font-bold"
                                            placeholder="Ej: iPhone 13 Pro">
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="space-y-2">
                                            <label
                                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Capacidad</label>
                                            <input type="text" name="trade_in_storage"
                                                class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 outline-none font-bold"
                                                placeholder="128GB">
                                        </div>
                                        <div class="space-y-2">
                                            <label
                                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Batería
                                                (%)</label>
                                            <input type="number" name="trade_in_battery_health"
                                                class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 outline-none font-bold"
                                                placeholder="85">
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">IMEI
                                            / Serial</label>
                                        <input type="text" name="trade_in_imei"
                                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 outline-none font-bold"
                                            placeholder="35XXXXXXXXXXXXX">
                                    </div>
                                    <div class="space-y-2">
                                        <label
                                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Valor
                                            Tomado</label>
                                        <div class="relative">
                                            <span
                                                class="absolute left-6 top-1/2 -translate-y-1/2 font-black text-indigo-500"
                                                x-text="currency"></span>
                                            <input type="number" step="0.01" name="trade_in_value"
                                                @input="discount = (parseFloat($event.target.value) || 0) + (parseFloat(discount) || 0)"
                                                class="w-full pl-16 pr-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent border-indigo-500/20 outline-none font-black text-indigo-500"
                                                placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Detalles
                                        / Estado</label>
                                    <textarea name="trade_in_notes" rows="2"
                                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 outline-none font-bold"
                                        placeholder="Pantalla cambiada, detalles estéticos..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-emerald-500/5 dark:shadow-none p-8 md:p-10">
                        <h2
                            class="text-xs font-black text-gray-400 uppercase tracking-[0.3em] flex items-center gap-2 italic mb-6">
                            <i data-lucide="file-text" class="w-4 h-4"></i> Observaciones de Venta
                        </h2>
                        <textarea name="notes" rows="3"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-emerald-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                            placeholder="Escribí aquí cualquier detalle extra del acuerdo..."></textarea>
                    </div>
                </div>

                <!-- Right Side: Totals & Summary -->
                <div class="space-y-8">

                    <!-- Sale Settings -->
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-emerald-500/5 dark:shadow-none p-8 md:p-10 space-y-6">
                        <h2
                            class="text-xs font-black text-emerald-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="settings" class="w-4 h-4"></i> Checkout
                        </h2>

                        <div class="space-y-6">
                            <!-- Client -->
                            <div class="space-y-2">
                                <div class="flex justify-between items-center ml-1">
                                    <label
                                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest italic">Cliente</label>
                                    <button type="button" @click="showQuickClientModal = true"
                                        class="text-[10px] font-black text-emerald-500 uppercase hover:underline">Nuevo
                                        Cliente</button>
                                </div>
                                <select id="sale-client-select" name="client_id" data-placeholder="Buscar cliente..."
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent outline-none font-bold appearance-none italic">
                                    <option value="">Consumidor Final</option>
                                    @foreach($clients as $cli)
                                        <option value="{{ $cli->id }}">{{ $cli->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Currency & Rate (Hidden but auto-populated) -->
                            <input type="hidden" name="currency" value="USD">
                            <input type="hidden" name="exchange_rate" :value="dolarRate || 1">

                            <!-- Auto Dolar Message -->
                            <div x-show="dolarRate" x-cloak
                                class="flex items-center gap-2 px-4 py-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl">
                                <i data-lucide="zap" class="w-4 h-4 text-emerald-500 animate-pulse"></i>
                                <p
                                    class="text-[10px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest leading-relaxed">
                                    Tomando cambio <span class="text-emerald-500">automático</span> de Dólar Blue a $<span
                                        x-text="dolarRate"></span>
                                </p>
                            </div>

                            <!-- Payment Method -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Método
                                    de Pago</label>
                                <select name="payment_method" required
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-900 dark:bg-dark text-white border border-transparent outline-none font-black italic appearance-none">
                                    <option value="cash">Efectivo</option>
                                    <option value="transfer">Transferencia</option>
                                    <option value="card">Tarjeta Crédito/Débito</option>
                                    <option value="crypto">USDT / Cripto</option>
                                </select>
                            </div>

                            <!-- Discount -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Descuento
                                    Total</label>
                                <input type="number" step="0.01" name="discount" x-model="discount"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent font-black italic outline-none text-emerald-600"
                                    placeholder="0.00">
                            </div>

                            <!-- Summary Block -->
                            <div
                                class="bg-gray-50 dark:bg-dark p-6 rounded-[30px] space-y-3 border border-gray-100 dark:border-white/5">
                                <div
                                    class="flex justify-between items-center text-[10px] font-black uppercase text-gray-400 tracking-widest">
                                    <span>Subtotal</span>
                                    <span x-text="`${currency} ${subtotal.toFixed(2)}`"></span>
                                </div>
                                <div
                                    class="flex justify-between items-center text-[10px] font-black uppercase text-gray-400 tracking-widest">
                                    <span>Descuento</span>
                                    <span x-text="`- ${currency} ${parseFloat(discount || 0).toFixed(2)}`"></span>
                                </div>
                                <div
                                    class="pt-3 border-t border-gray-200 dark:border-white/10 flex justify-between items-center">
                                    <span class="text-xs font-black uppercase italic text-gray-900 dark:text-gray-100">Total
                                        a Pagar</span>
                                    <div class="text-right">
                                        <div class="text-2xl font-black text-emerald-600 italic tracking-tighter"
                                            x-text="`${currency} ${total.toFixed(2)}`"></div>
                                        <div x-show="dolarRate"
                                            class="text-[10px] font-bold text-gray-400 mt-1 uppercase tracking-widest">
                                            ≈ <span
                                                x-text="`$${(total * dolarRate).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`"></span>
                                            ARS
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="space-y-4">
                        <button type="submit" :disabled="items.length == 0"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black py-5 rounded-3xl shadow-2xl shadow-emerald-500/30 active:scale-95 transition-all text-xs uppercase tracking-[0.2em] flex items-center justify-center gap-3 italic">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                            Finalizar Venta
                        </button>
                        <p class="text-[10px] text-center font-bold text-gray-400 italic">Al finalizar se descontará el
                            stock automáticamente.</p>
                    </div>
                </div>

            </div>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const quickClientForm = document.getElementById('sale-quick-client-form');
            const clientSelect = document.getElementById('sale-client-select');
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
                    window.dispatchEvent(new CustomEvent('close-quick-client-sale'));
                } catch (error) {
                    alert('No se pudo crear el cliente. Revisa los datos e inténtalo nuevamente.');
                }
            });
        });
    </script>
@endsection