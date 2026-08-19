{{--
    $suppliers: Collection de proveedores activos de la organizacion.
    $selected: array [supplier_id => ['supplier_sku' => ..., 'cost' => ..., 'is_primary' => bool]] ya vinculados.
--}}
<div class="space-y-4">
    <label class="text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1">Proveedores</label>

    @if($suppliers->isEmpty())
        <p class="text-xs text-gray-400 font-medium ml-1">
            No hay proveedores activos todavía.
            <a href="{{ route('supplier.create') }}" class="text-primary underline">Creá uno acá</a>.
        </p>
    @else
        <div class="rounded-2xl border border-gray-100 dark:border-white/5 divide-y divide-gray-50 dark:divide-white/5 overflow-hidden">
            @foreach($suppliers as $supplier)
                @php $row = $selected[$supplier->id] ?? null; @endphp
                <div class="p-4 grid grid-cols-1 md:grid-cols-[auto_1fr_140px_120px_100px] gap-3 items-center bg-white dark:bg-dark-alt">
                    <input type="checkbox" name="suppliers[{{ $supplier->id }}][selected]" value="1"
                        id="supplier-{{ $supplier->id }}" {{ $row ? 'checked' : '' }}
                        class="w-5 h-5 rounded-lg text-primary focus:ring-primary">

                    <label for="supplier-{{ $supplier->id }}" class="text-sm font-bold text-gray-700 dark:text-gray-200">
                        {{ $supplier->business_name }}
                    </label>

                    <input type="text" name="suppliers[{{ $supplier->id }}][supplier_sku]"
                        value="{{ $row['supplier_sku'] ?? '' }}" placeholder="SKU proveedor"
                        class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none text-xs font-bold">

                    <input type="number" step="0.01" min="0" name="suppliers[{{ $supplier->id }}][cost]"
                        value="{{ $row['cost'] ?? '' }}" placeholder="Costo"
                        class="px-3 py-2 rounded-xl bg-gray-100 dark:bg-dark border border-transparent focus:border-primary/50 outline-none text-xs font-bold">

                    <label class="flex items-center gap-2 text-[10px] font-black uppercase text-gray-400">
                        <input type="checkbox" name="suppliers[{{ $supplier->id }}][is_primary]" value="1"
                            {{ !empty($row['is_primary']) ? 'checked' : '' }}
                            class="w-4 h-4 rounded text-primary focus:ring-primary">
                        Principal
                    </label>
                </div>
            @endforeach
        </div>
        @error('suppliers') <p class="text-[10px] text-red-500 font-bold ml-1">{{ $message }}</p> @enderror
    @endif
</div>
