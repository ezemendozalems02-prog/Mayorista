@extends('layouts.admin')

@section('title', 'Clientes')

@section('content')
    <div class="space-y-6 md:space-y-8 animate-in transition-all duration-500">

        <!-- Notifications -->
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="fixed bottom-8 right-8 z-[100] bg-emerald-500 text-white px-8 py-4 rounded-3xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-500 font-black uppercase text-xs tracking-widest italic">
                <i data-lucide="check-circle" class="w-5 h-5"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters and Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex-1 flex flex-col md:flex-row items-center gap-4 w-full">
                <!-- Search -->
                <div class="relative w-full md:w-80 group">
                    <form action="{{ route('client.index') }}" method="GET">
                        <i data-lucide="search"
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 group-focus-within:text-primary transition-colors"></i>
                        <input name="search" value="{{ request('search') }}" type="text"
                            placeholder="Nombre, Teléfono o DNI..."
                            class="w-full pl-11 pr-4 py-3 rounded-2xl bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 outline-none focus:border-primary transition-all shadow-sm text-sm font-medium">
                    </form>
                </div>

                <!-- Filter Toggle -->
                <div
                    class="flex items-center bg-white dark:bg-dark-alt p-1.5 rounded-2xl border border-gray-100 dark:border-white/5 shadow-sm">
                    <a href="{{ route('client.index') }}"
                        class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ !request('filter') ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-400 hover:text-gray-600' }}">
                        Todos </a>
                    <a href="{{ route('client.index', ['filter' => 'best']) }}"
                        class="px-5 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ request('filter') == 'best' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-400 hover:text-gray-600' }}">
                        Mejores Clientes </a>
                </div>

                <!-- Client Type Filter -->
                <div
                    class="flex items-center bg-white dark:bg-dark-alt p-1.5 rounded-2xl border border-gray-100 dark:border-white/5 shadow-sm">
                    <a href="{{ route('client.index', request()->except('client_type')) }}"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ !request('client_type') ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-400 hover:text-gray-600' }}">
                        Todos los tipos </a>
                    <a href="{{ route('client.index', array_merge(request()->query(), ['client_type' => 'retail'])) }}"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ request('client_type') == 'retail' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-400 hover:text-gray-600' }}">
                        Minoristas </a>
                    <a href="{{ route('client.index', array_merge(request()->query(), ['client_type' => 'wholesale'])) }}"
                        class="px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all {{ request('client_type') == 'wholesale' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-gray-400 hover:text-gray-600' }}">
                        Mayoristas </a>
                </div>
            </div>

            <a href="{{ route('client.create') }}"
                class="bg-primary hover:bg-primary/90 text-white px-8 py-3 rounded-2xl shadow-xl shadow-primary/20 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center justify-center gap-2 group w-full md:w-auto">
                <i data-lucide="plus" class="w-4 h-4 group-hover:rotate-90 transition-all"></i>
                Nuevo Cliente
            </a>
        </div>

        <!-- Table Container -->
        <div
            class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden relative">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr
                            class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-[10px] text-gray-400 font-black uppercase tracking-[0.2em] italic">
                            <th class="px-8 py-5 text-left">Info. de Cliente</th>
                            <th class="px-8 py-5 text-left hidden lg:table-cell">Tipo</th>
                            <th class="px-8 py-5 text-left hidden md:table-cell">Documento</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Compras</th>
                            <th class="px-8 py-5 text-left hidden sm:table-cell">Total Invertido</th>
                            <th class="px-8 py-5 text-left hidden lg:table-cell">Saldo</th>
                            <th class="px-8 py-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($clients as $client)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all group">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-12 h-12 rounded-2xl bg-primary/10 flex items-center justify-center border border-primary/5 shadow-sm group-hover:scale-110 transition-transform">
                                            <span
                                                class="text-primary text-sm font-black uppercase">{{ $client->full_name[0] }}</span>
                                        </div>
                                        <div>
                                            <p
                                                class="text-sm font-black text-gray-900 dark:text-gray-100 group-hover:text-primary transition-colors italic tracking-tight">
                                                {{ $client->display_name }}
                                            </p>
                                            <p
                                                class="text-[10px] text-gray-400 font-bold flex items-center gap-1 mt-1 uppercase tracking-tighter italic">
                                                <i data-lucide="phone" class="w-3 h-3 text-primary"></i>
                                                {{ $client->phone ?? 'Sin teléfono' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 hidden lg:table-cell">
                                    @if($client->client_type?->value === 'wholesale')
                                        <span class="text-[10px] font-black uppercase bg-primary/10 text-primary px-3 py-1 rounded-full border border-primary/20">Mayorista</span>
                                    @else
                                        <span class="text-[10px] font-black uppercase bg-gray-400/10 text-gray-500 px-3 py-1 rounded-full">Minorista</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-xs font-bold text-gray-500 dark:text-gray-400 italic hidden md:table-cell">
                                    {{ $client->cuit ?? $client->document_number ?? '-' }}
                                </td>
                                <td class="px-8 py-6 hidden sm:table-cell">
                                    <span
                                        class="text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-600 px-3 py-1 rounded-full border border-emerald-500/20 shadow-sm">
                                        {{ $client->sales_count }} Compras
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-sm font-black text-gray-700 dark:text-gray-200 italic hidden sm:table-cell">
                                    ${{ number_format($client->sales_sum_total ?? 0, 2, ',', '.') }}
                                </td>
                                <td class="px-8 py-6 hidden lg:table-cell">
                                    @php $balance = $client->account_movements_sum_amount ?? 0; @endphp
                                    @if($balance != 0)
                                        <a href="{{ route('client.account', $client) }}" class="text-sm font-black italic {{ $balance > 0 ? 'text-red-500' : 'text-emerald-600' }} hover:underline">
                                            ${{ number_format(abs($balance), 2, ',', '.') }}
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-300 italic">Al día</span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('client.show', $client) }}"
                                            class="p-2.5 bg-gray-50 dark:bg-dark hover:bg-emerald-500 hover:text-white transition-all rounded-xl border border-transparent shadow-sm group/btn">
                                            <i data-lucide="eye"
                                                class="w-4 h-4 transition-transform group-hover/btn:scale-110"></i>
                                        </a>
                                        <a href="{{ route('client.edit', $client) }}"
                                            class="p-2.5 bg-gray-50 dark:bg-dark hover:bg-primary hover:text-white transition-all rounded-xl border border-transparent shadow-sm group/btn">
                                            <i data-lucide="edit-3"
                                                class="w-4 h-4 transition-transform group-hover/btn:rotate-12"></i>
                                        </a>
                                    </div>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-20 text-center animate-pulse">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <i data-lucide="user-plus" class="w-16 h-16 mb-4 opacity-20"></i>
                                        <p class="text-lg font-black tracking-tight italic">No encontramos clientes</p>
                                        <p class="text-xs uppercase font-bold mt-1 opacity-50 tracking-widest">Empezá
                                            registrando tu primer cliente hoy</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Modern Pagination -->
            <div class="p-8 bg-gray-50/30 dark:bg-white/5 border-t border-gray-100 dark:border-white/5">
                {{ $clients->links() }}
            </div>
        </div>
    </div>

    <!-- Styles override for Pagination (Laravel 9 default uses Tailwind) -->
    <style>
        /* Add subtle shadow to the whole panel */
        table thead th {
            vertical-align: middle;
        }

        .pagination-container svg {
            height: 1.5rem;
            width: 1.5rem;
        }
    </style>
@endsection