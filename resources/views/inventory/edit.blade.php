@extends('layouts.admin')

@section('title', 'Editar Equipo')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500 pb-20">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3">
                    <i data-lucide="edit-3" class="w-8 h-8 text-indigo-500"></i> Editar Equipo
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Actualizá la información técnica y de precio
                    del equipo.</p>
            </div>
            <a href="{{ route('inventory.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        @if ($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 p-6 rounded-3xl">
                <ul class="list-disc list-inside text-sm text-red-600 font-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Form Preview (Dynamic) -->
        <div id="equipment-preview" class="animate-in fade-in slide-in-from-top-4 duration-500">
            <div class="bg-indigo-600/10 border border-indigo-500/20 rounded-3xl p-6 flex items-center gap-6">
                <div class="w-16 h-16 bg-indigo-600 rounded-2xl flex items-center justify-center shadow-xl shadow-indigo-600/20">
                    <i data-lucide="smartphone" class="w-8 h-8 text-white"></i>
                </div>
                <div>
                    <h3 class="text-xs font-black text-indigo-500 uppercase tracking-widest italic mb-1">Vista Previa del Equipo</h3>
                    <p id="preview-name" class="text-xl font-black dark:text-white uppercase italic tracking-tight">Cargando...</p>
                    <p id="preview-specs" class="text-xs text-gray-500 font-bold uppercase tracking-widest mt-1">Seleccione las especificaciones</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form action="{{ route('inventory.update', $item) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Technical Specs Column -->
                <div class="lg:col-span-2 space-y-8">
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-indigo-500/5 dark:shadow-none p-8 md:p-10 space-y-8">
                        <h2
                            class="text-xs font-black text-indigo-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="cpu" class="w-4 h-4"></i> Especificaciones del Hardware
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Brand -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Marca</label>
                                <select name="brand" id="brand-select" data-search-disabled
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                    <option value="Apple" {{ $item->brand == 'Apple' ? 'selected' : '' }}>Apple</option>
                                    <option value="Samsung" {{ $item->brand == 'Samsung' ? 'selected' : '' }}>Samsung</option>
                                    <option value="Xiaomi" {{ $item->brand == 'Xiaomi' ? 'selected' : '' }}>Xiaomi</option>
                                    <option value="Accesorios" {{ $item->brand == 'Accesorios' ? 'selected' : '' }}>Accesorios</option>
                                    <option value="Otros" {{ $item->brand == 'Otros' ? 'selected' : '' }}>Otros</option>
                                </select>
                            </div>

                            <!-- Model -->
                            <div class="space-y-2 lg:col-span-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Modelo
                                    del Equipo <span class="text-red-500">*</span></label>
                                <div id="model-select-wrapper">
                                    <select name="model" id="model-select" required data-search-disabled
                                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                        <option value="{{ $item->model }}">{{ $item->model }}</option>
                                    </select>
                                </div>
                                <div id="custom-model-wrapper" class="hidden mt-2">
                                    <input type="text" id="custom-model-input" value="{{ $item->model }}"
                                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                        placeholder="Escriba el modelo manualmente...">
                                </div>
                            </div>

                            <!-- Storage -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Almacenamiento</label>
                                @php
                                    $predefinedStorages = ['64 GB', '128 GB', '256 GB', '512 GB', '1 TB', '2 TB'];
                                    $isCustomStorage = !in_array($item->storage, $predefinedStorages);
                                @endphp
                                <select name="storage" id="storage-select" data-search-disabled
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                    @foreach($predefinedStorages as $storage)
                                        <option value="{{ $storage }}" {{ $item->storage == $storage ? 'selected' : '' }}>{{ $storage }}</option>
                                    @endforeach
                                    <option value="custom" {{ $isCustomStorage ? 'selected' : '' }}>Otro (Manual)</option>
                                </select>
                                <div id="custom-storage-wrapper" class="{{ $isCustomStorage ? '' : 'hidden' }} mt-2">
                                    <input type="text" id="custom-storage-input" value="{{ $item->storage }}"
                                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                        placeholder="Ej: 32 GB">
                                </div>
                            </div>

                            <!-- Color -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Color</label>
                                <div id="color-select-wrapper">
                                    <select name="color" id="color-select" data-search-disabled
                                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                        <option value="{{ $item->color }}">{{ $item->color }}</option>
                                    </select>
                                </div>
                                <div id="custom-color-wrapper" class="hidden mt-2">
                                    <input type="text" id="custom-color-input" value="{{ $item->color }}"
                                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                        placeholder="Ej: Color especial">
                                </div>
                            </div>

                            <!-- IMEI -->
                            <div class="space-y-2 {{ $item->category === 'accessory' ? 'hidden' : '' }}" id="imei-container">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">IMEI</label>
                                <input type="text" name="imei" value="{{ old('imei', $item->imei) }}"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="15 dígitos">
                            </div>

                            <!-- Serial Number -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Número
                                    de Serie</label>
                                <input type="text" name="serial_number"
                                    value="{{ old('serial_number', $item->serial_number) }}"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="Serial ID">
                            </div>
                        </div>
                    </div>

                    <!-- Condition & Health -->
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-indigo-500/5 dark:shadow-none p-8 md:p-10 space-y-8">
                        <h2
                            class="text-xs font-black text-emerald-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="sparkles" class="w-4 h-4"></i> Estado y Condición
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Battery Health -->
                            <div class="space-y-2 {{ $item->category === 'accessory' ? 'hidden' : '' }}" id="battery-container">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Salud
                                    de Batería (%)</label>
                                <input type="number" name="battery_health"
                                    value="{{ old('battery_health', $item->battery_health) }}" min="0" max="100"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="Ej: 98">
                            </div>

                            <!-- Cosmetic Condition -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Condición
                                    Estética</label>
                                <select name="cosmetic_condition"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                    <option value="Nuevo" {{ $item->cosmetic_condition == 'Nuevo' ? 'selected' : '' }}>Nuevo
                                        (Sellado)</option>
                                    <option value="Como Nuevo" {{ $item->cosmetic_condition == 'Como Nuevo' ? 'selected' : '' }}>Como Nuevo (9.9/10)</option>
                                    <option value="Excelente" {{ $item->cosmetic_condition == 'Excelente' ? 'selected' : '' }}>Excelente (9/10)</option>
                                    <option value="Buen Estado" {{ $item->cosmetic_condition == 'Buen Estado' ? 'selected' : '' }}>Buen Estado (7-8/10)</option>
                                    <option value="Detalles" {{ $item->cosmetic_condition == 'Detalles' ? 'selected' : '' }}>
                                        Con Detalles</option>
                                </select>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Notas
                                / Observaciones</label>
                            <textarea name="notes" rows="4"
                                class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="Escribe aquí detalles adicionales...">{{ old('notes', $item->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Prices & Status Column -->
                <div class="space-y-8">
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-indigo-500/5 dark:shadow-none p-8 md:p-10 space-y-6">
                        <h2
                            class="text-xs font-black text-indigo-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="dollar-sign" class="w-4 h-4"></i> Precios y Venta
                        </h2>

                        <div class="space-y-6">
                            <!-- Category -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Categoría</label>
                                <select name="category" id="category-select"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                    <option value="iphone" {{ $item->category == 'iphone' ? 'selected' : '' }}>iPhone / Smartphone</option>
                                    <option value="ipad" {{ $item->category == 'ipad' ? 'selected' : '' }}>iPad / Tablet</option>
                                    <option value="macbook" {{ $item->category == 'macbook' ? 'selected' : '' }}>MacBook / Laptop</option>
                                    <option value="accessory" {{ $item->category == 'accessory' ? 'selected' : '' }}>Accesorio</option>
                                    <option value="traded_in" {{ $item->category == 'traded_in' ? 'selected' : '' }}>Canje</option>
                                </select>
                            </div>

                            <!-- Purchase Price -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Costo
                                    de Compra</label>
                                <input type="number" step="0.01" name="purchase_price"
                                    value="{{ old('purchase_price', $item->purchase_price) }}" required
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="0.00">
                            </div>

                            <!-- Sale Price -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Precio
                                    de Venta Sugerido</label>
                                <input type="number" step="0.01" name="sale_price"
                                    value="{{ old('sale_price', $item->sale_price) }}" required
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="0.00">
                            </div>

                            <!-- Currency -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Moneda</label>
                                <select name="currency"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-indigo-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                    <option value="USD" {{ $item->currency == 'USD' ? 'selected' : '' }}>Dólares (USD)
                                    </option>
                                    <option value="ARS" {{ $item->currency == 'ARS' ? 'selected' : '' }}>Pesos (ARS)</option>
                                </select>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Estado
                                    del Equipo</label>
                                <select name="status"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-900 text-white border-transparent outline-none font-bold appearance-none">
                                    <option value="in_stock" {{ $item->status->value == 'in_stock' ? 'selected' : '' }}>En
                                        Stock</option>
                                    <option value="reserved" {{ $item->status->value == 'reserved' ? 'selected' : '' }}>
                                        Reservado</option>
                                    <option value="sold" {{ $item->status->value == 'sold' ? 'selected' : '' }}>Vendido
                                    </option>
                                    <option value="in_service" {{ $item->status->value == 'in_service' ? 'selected' : '' }}>En
                                        Servicio</option>
                                    <option value="archived" {{ $item->status->value == 'archived' ? 'selected' : '' }}>
                                        Archivado / Eliminado</option>
                                </select>
                            </div>

                            <!-- Stock Type -->
                            <input type="hidden" name="stock_type" value="{{ $item->stock_type }}">
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="space-y-4">
                        <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-5 rounded-3xl shadow-2xl shadow-indigo-500/30 active:scale-95 transition-all text-sm uppercase tracking-[0.2em] flex items-center justify-center gap-3">
                            <i data-lucide="save" class="w-5 h-5"></i>
                            Guardar Cambios
                        </button>
                        <a href="{{ route('inventory.index') }}"
                            class="block w-full text-center py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-indigo-500 transition-colors italic">Descartar
                            cambios</a>
                    </div>
                </div>

            </div>
        </form>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneData = {
                'Apple': {
                    models: [
                        'iPhone 11', 'iPhone 11 Pro', 'iPhone 11 Pro Max',
                        'iPhone 12', 'iPhone 12 Mini', 'iPhone 12 Pro', 'iPhone 12 Pro Max',
                        'iPhone 13', 'iPhone 13 Mini', 'iPhone 13 Pro', 'iPhone 13 Pro Max',
                        'iPhone 14', 'iPhone 14 Plus', 'iPhone 14 Pro', 'iPhone 14 Pro Max',
                        'iPhone 15', 'iPhone 15 Plus', 'iPhone 15 Pro', 'iPhone 15 Pro Max',
                        'iPhone 16', 'iPhone 16 Plus', 'iPhone 16 Pro', 'iPhone 16 Pro Max',
                        'iPhone 17', 'iPhone 17 Plus', 'iPhone 17 Pro', 'iPhone 17 Pro Max'
                    ],
                    colors: ['Negro', 'Blanco', 'Azul', 'Verde', 'Amarillo', 'Rojo', 'Dorado', 'Silver', 'Grafito', 'Titanio Natural', 'Titanio Azul', 'Titanio Negro'],
                    category: 'iphone'
                },
                'Samsung': {
                    models: [
                        'S21', 'S21+', 'S21 Ultra',
                        'S22', 'S22+', 'S22 Ultra',
                        'S23', 'S23+', 'S23 Ultra',
                        'S24', 'S24+', 'S24 Ultra',
                        'Z Flip 3', 'Z Flip 4', 'Z Flip 5', 'Z Flip 6',
                        'Z Fold 3', 'Z Fold 4', 'Z Fold 5', 'Z Fold 6'
                    ],
                    colors: ['Phantom Black', 'Cream', 'Green', 'Lavender', 'Graphite', 'Sky Blue'],
                    category: 'iphone'
                },
                'Xiaomi': {
                    models: [
                        'Xiaomi 11', 'Xiaomi 11 Pro', 'Xiaomi 11 Ultra',
                        'Xiaomi 12', 'Xiaomi 12 Pro', 'Xiaomi 12T Pro',
                        'Xiaomi 13', 'Xiaomi 13 Pro', 'Xiaomi 13 Ultra',
                        'Xiaomi 14', 'Xiaomi 14 Pro', 'Xiaomi 14 Ultra'
                    ],
                    colors: ['Negro', 'Blanco', 'Azul', 'Verde', 'Gris'],
                    category: 'iphone'
                },
                'Accesorios': {
                    models: [
                        'Cargador 20W USB-C', 'Cable Lightning', 'Funda Silicona', 
                        'EarPods', 'AirPods Pro', 'Apple Watch Band', 
                        'Mica Protectora', 'Vidrio Templado'
                    ],
                    colors: ['Blanco', 'Negro', 'Transparente', 'Azul', 'Rojo', 'Midnight', 'Starlight'],
                    category: 'accessory'
                },
                'Otros': {
                    models: [],
                    colors: [],
                    category: 'iphone'
                }
            };

            const brandSelect = document.getElementById('brand-select');
            const modelSelect = document.getElementById('model-select');
            const colorSelect = document.getElementById('color-select');
            const storageSelect = document.getElementById('storage-select');
            const categorySelect = document.getElementById('category-select');

            const customModelWrapper = document.getElementById('custom-model-wrapper');
            const customModelInput = document.getElementById('custom-model-input');
            const customColorWrapper = document.getElementById('custom-color-wrapper');
            const customColorInput = document.getElementById('custom-color-input');
            const customStorageWrapper = document.getElementById('custom-storage-wrapper');
            const customStorageInput = document.getElementById('custom-storage-input');

            const previewSection = document.getElementById('equipment-preview');
            const previewName = document.getElementById('preview-name');
            const previewSpecs = document.getElementById('preview-specs');

            let modelTomSelect, colorTomSelect, brandTomSelect, storageTomSelect, categoryTomSelect;

            function updatePreview() {
                const brand = brandSelect.value || '';
                let model = modelSelect.value === 'custom' ? customModelInput.value : modelSelect.value;
                let storage = storageSelect.value === 'custom' ? customStorageInput.value : storageSelect.value;
                let color = colorSelect.value === 'custom' ? customColorInput.value : colorSelect.value;

                previewName.textContent = `${brand === 'Accesorios' ? '' : brand} ${model}`.trim() || 'Editar Registro';
                previewSpecs.textContent = `${storage || '—'} • ${color || '—'}`;
            }

            function toggleFieldsByCategory(category) {
                const imeiContainer = document.getElementById('imei-container');
                const batteryContainer = document.getElementById('battery-container');

                if (category === 'accessory') {
                    imeiContainer.classList.add('hidden');
                    batteryContainer.classList.add('hidden');
                } else {
                    imeiContainer.classList.remove('hidden');
                    batteryContainer.classList.remove('hidden');
                }
            }

            function initTomSelects() {
                if (typeof TomSelect === 'undefined') return;

                brandTomSelect = new TomSelect(brandSelect, { create: false });
                
                storageTomSelect = new TomSelect(storageSelect, {
                    create: false,
                    onChange: function(value) {
                        if (value === 'custom') {
                            customStorageWrapper.classList.remove('hidden');
                            customStorageInput.name = 'storage';
                            storageSelect.removeAttribute('name');
                        } else {
                            customStorageWrapper.classList.add('hidden');
                            customStorageInput.removeAttribute('name');
                            storageSelect.setAttribute('name', 'storage');
                        }
                        updatePreview();
                    }
                });

                modelTomSelect = new TomSelect(modelSelect, {
                    create: false,
                    sortField: { field: "text", direction: "asc" },
                    onChange: function(value) {
                        if (value === 'custom') {
                            customModelWrapper.classList.remove('hidden');
                            customModelInput.required = true;
                            customModelInput.name = 'model';
                            modelSelect.removeAttribute('name');
                        } else {
                            customModelWrapper.classList.add('hidden');
                            customModelInput.required = false;
                            customModelInput.removeAttribute('name');
                            modelSelect.setAttribute('name', 'model');
                        }
                        updatePreview();
                    }
                });

                colorTomSelect = new TomSelect(colorSelect, {
                    create: false,
                    onChange: function(value) {
                        if (value === 'custom') {
                            customColorWrapper.classList.remove('hidden');
                            customColorInput.name = 'color';
                            colorSelect.removeAttribute('name');
                        } else {
                            customColorWrapper.classList.add('hidden');
                            customColorInput.removeAttribute('name');
                            colorSelect.setAttribute('name', 'color');
                        }
                        updatePreview();
                    }
                });

                categoryTomSelect = new TomSelect(categorySelect, { 
                    create: false,
                    onChange: function(value) {
                        toggleFieldsByCategory(value);
                    }
                });
                
                // Trigger initial setup
                setupBrandData('{{ $item->brand }}', '{{ $item->model }}', '{{ $item->color }}');
            }

            function setupBrandData(brand, currentModel, currentColor) {
                const data = phoneData[brand] || { models: [], colors: [], category: 'iphone' };

                if (!modelTomSelect || !colorTomSelect) return;

                // Update category if it's a new brand selection
                if (!currentModel && data.category) {
                   categoryTomSelect.setValue(data.category);
                }

                // Update Models
                modelTomSelect.clearOptions();
                
                if (brand === 'Otros') {
                    modelTomSelect.addOption({ value: 'custom', text: 'Escribir manual...' });
                    modelTomSelect.setValue('custom');
                } else if (brand) {
                    data.models.forEach(model => {
                        modelTomSelect.addOption({ value: model, text: model });
                    });
                    
                    if (currentModel && !data.models.includes(currentModel) && currentModel !== 'custom') {
                         modelTomSelect.addOption({ value: currentModel, text: currentModel });
                    }
                    
                    modelTomSelect.addOption({ value: 'custom', text: 'Otro modelo (escribir manual)' });
                    modelTomSelect.refreshOptions(false);
                    modelTomSelect.setValue(currentModel || modelTomSelect.getValue());
                }

                // Update Colors
                colorTomSelect.clearOptions();
                
                if (brand === 'Otros') {
                    colorTomSelect.addOption({ value: 'custom', text: 'Escribir manual...' });
                    colorTomSelect.setValue('custom');
                } else if (brand) {
                    data.colors.forEach(color => {
                        colorTomSelect.addOption({ value: color, text: color });
                    });
                    
                    if (currentColor && !data.colors.includes(currentColor) && currentColor !== 'custom') {
                        colorTomSelect.addOption({ value: currentColor, text: currentColor });
                    }
                    
                    colorTomSelect.addOption({ value: 'custom', text: 'Otro color (manual)' });
                    colorTomSelect.refreshOptions(false);
                    colorTomSelect.setValue(currentColor || colorTomSelect.getValue());
                }
                
                if (modelTomSelect.getValue() === 'custom') customModelWrapper.classList.remove('hidden');
                if (colorTomSelect.getValue() === 'custom') customColorWrapper.classList.remove('hidden');
                
                updatePreview();
            }

            initTomSelects();

            brandSelect.addEventListener('change', function() {
                setupBrandData(this.value, null, null);
            });
            
            // Initial check for storage
            if (storageSelect.value === 'custom') {
                customStorageWrapper.classList.remove('hidden');
                customStorageInput.name = 'storage';
                storageSelect.removeAttribute('name');
            }
            
            // Listen to manual inputs for preview
            [customModelInput, customColorInput, customStorageInput].forEach(el => {
                el.addEventListener('input', updatePreview);
            });
        });
    </script>
@endsection