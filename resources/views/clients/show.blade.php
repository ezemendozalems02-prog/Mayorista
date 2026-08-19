@extends('layouts.admin')

@section('title', 'Detalle de Cliente')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500 pb-20">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-16 h-16 rounded-[24px] bg-primary/10 border border-primary/20 flex items-center justify-center shadow-xl">
                    <span class="text-2xl font-black text-primary uppercase">{{ $client->full_name[0] }}</span>
                </div>
                <div>
                    <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 italic">{{ $client->full_name }}</h1>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3 h-3"></i> Cliente desde
                            {{ optional($client->created_at)->format('M Y') ?? 'N/A' }}
                        </span>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span
                            class="text-xs font-bold text-emerald-500 uppercase tracking-widest">{{ $client->sales->count() }}
                            Compras Realizadas</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('client.edit', $client) }}"
                    class="px-6 py-3 bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-white/5 transition-all shadow-sm flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4 text-primary"></i> Editar Perfil
                </a>
                <a href="{{ route('client.index') }}"
                    class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                    <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Stats & Info -->
            <div class="space-y-8">
                <!-- Info Card -->
                <div
                    class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 p-8 space-y-6 shadow-2xl shadow-primary/5">
                    <h3
                        class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] italic flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4"></i> Datos de Contacto
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-dark">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                                <i data-lucide="phone" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Teléfono</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $client->phone ?? 'No registrado' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-dark">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                                <i data-lucide="mail" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Email</p>
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $client->email ?? 'No registrado' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-dark">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                                <i data-lucide="fingerprint" class="w-5 h-5 text-primary"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Documento / DNI
                                </p>
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-100">
                                    {{ $client->document_number ?? 'No registrado' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Stats -->
                <div
                    class="bg-primary rounded-[40px] p-8 text-white space-y-6 shadow-2xl shadow-primary/30 relative overflow-hidden">
                    <i data-lucide="trending-up" class="absolute -right-4 -bottom-4 w-32 h-32 opacity-10"></i>
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] italic opacity-60">Resumen Financiero</h3>
                    <div>
                        <p class="text-xs font-bold opacity-80 uppercase tracking-widest">Total Invertido</p>
                        <p class="text-4xl font-black italic tracking-tighter mt-1">USD
                            {{ number_format($client->sales->sum('total'), 2) }}</p>
                    </div>
                    <div class="pt-6 border-t border-white/10 flex justify-between items-center">
                        <div>
                            <p class="text-[10px] font-black uppercase opacity-60">Ticket Promedio</p>
                            <p class="text-lg font-black italic">USD
                                {{ $client->sales->count() > 0 ? number_format($client->sales->sum('total') / $client->sales->count(), 2) : '0.00' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black uppercase opacity-60">Ventas</p>
                            <p class="text-lg font-black italic">{{ $client->sales->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Sales History -->
            <div class="lg:col-span-2 space-y-8">
                <div
                    class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none overflow-hidden">
                    <div class="p-8 border-b border-gray-100 dark:border-white/5 flex items-center justify-between">
                        <h3
                            class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2">
                            <i data-lucide="history" class="w-5 h-5 text-primary"></i> Historial de Compras
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr
                                    class="text-[10px] text-gray-400 font-black uppercase tracking-widest bg-gray-50/50 dark:bg-white/5 italic">
                                    <th class="px-8 py-5 text-left">Fecha</th>
                                    <th class="px-8 py-5 text-left">Comprobante</th>
                                    <th class="px-8 py-5 text-left">Artículos</th>
                                    <th class="px-8 py-5 text-right">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                                @forelse($client->sales as $sale)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all">
                                        <td class="px-8 py-6 text-xs font-bold text-gray-500 underline decoration-primary/20">
                                            {{ optional($sale->sold_at)->format('d/m/Y') ?? 'N/A' }}
                                        </td>
                                        <td class="px-8 py-6">
                                            <a href="{{ route('sale.show', $sale) }}"
                                                class="text-sm font-black text-gray-900 dark:text-gray-100 hover:text-primary transition-colors italic">
                                                #{{ $sale->sale_number }}
                                            </a>
                                        </td>
                                        <td class="px-8 py-6">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($sale->items as $item)
                                                    <span
                                                        class="text-[9px] font-black uppercase px-2 py-0.5 bg-gray-100 dark:bg-white/5 rounded-md text-gray-400">
                                                        {{ $item->inventoryItem->model ?? $item->item_name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-8 py-6 text-right">
                                            <span class="text-sm font-black text-emerald-600 italic">
                                                {{ $sale->currency }} {{ number_format($sale->total, 2) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-20 text-center">
                                            <p class="text-sm font-bold text-gray-300 uppercase italic">Aún no se registraron
                                                ventas para este cliente.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection