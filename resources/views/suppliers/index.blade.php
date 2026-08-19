@extends('layouts.admin')

@section('title', 'Proveedores')

@section('content')
    <div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
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

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="relative w-full md:w-80 group">
                <form action="{{ route('supplier.index') }}" method="GET">
                    <i data-lucide="search"
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                    <input name="search" value="{{ request('search') }}" type="text" placeholder="Razón social, CUIT..."
                        class="w-full pl-11 pr-4 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none focus:border-primary transition-all shadow-sm text-sm font-medium">
                </form>
            </div>

            <a href="{{ route('supplier.create') }}"
                class="bg-primary hover:bg-primary/90 text-white px-8 py-3 rounded-2xl shadow-xl shadow-primary/20 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 group w-full md:w-auto">
                <i data-lucide="plus" class="w-4 h-4 group-hover:rotate-90 transition-all"></i>
                Nuevo Proveedor
            </a>
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Proveedor</th>
                            <th class="px-8 py-5 text-left hidden md:table-cell">Contacto</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Productos</th>
                            <th class="px-8 py-5 text-left">Estado</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($suppliers as $supplier)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all group">
                                <td class="px-8 py-6">
                                    <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $supplier->business_name }}</p>
                                    <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-tighter italic">{{ $supplier->cuit ?? 'Sin CUIT' }}</p>
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-gray-500 dark:text-gray-400 italic hidden md:table-cell">
                                    {{ $supplier->phone ?? $supplier->whatsapp ?? $supplier->email ?? '—' }}
                                </td>
                                <td class="px-8 py-6 hidden sm:table-cell">
                                    <span class="text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-600 px-3 py-1 rounded-full border border-emerald-500/20 shadow-sm">
                                        {{ $supplier->products_count }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    @if($supplier->is_active)
                                        <span class="text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-600 px-3 py-1 rounded-full">Activo</span>
                                    @else
                                        <span class="text-[10px] font-black uppercase bg-gray-400/10 text-gray-500 px-3 py-1 rounded-full">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('supplier.edit', $supplier) }}"
                                            class="p-2.5 bg-gray-50 dark:bg-dark hover:bg-primary hover:text-white transition-all rounded-xl border border-transparent shadow-sm group/btn">
                                            <i data-lucide="edit-3" class="w-4 h-4 transition-transform group-hover/btn:rotate-12"></i>
                                        </a>
                                        <form action="{{ route('supplier.destroy', $supplier) }}" method="POST" onsubmit="return confirm('¿Eliminar este proveedor?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="p-2.5 bg-gray-50 dark:bg-dark hover:bg-red-500 hover:text-white transition-all rounded-xl border border-transparent shadow-sm group/btn">
                                                <i data-lucide="trash-2" class="w-4 h-4 transition-transform group-hover/btn:scale-110"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-20 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="truck" class="w-16 h-16 mb-4 opacity-20"></i>
                                        <p class="text-lg font-black tracking-tight italic">No hay proveedores todavía</p>
                                        <p class="text-xs uppercase font-bold mt-1 opacity-50 tracking-widest">Registrá tu primer proveedor</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-8 bg-gray-50/30 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
                {{ $suppliers->links() }}
            </div>
        </div>
    </div>
@endsection
