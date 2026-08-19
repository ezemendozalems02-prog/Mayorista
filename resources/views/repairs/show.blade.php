@extends('layouts.admin')

@section('title', 'Orden de Reparación')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500 pb-20">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div
                    class="w-16 h-16 rounded-[24px] bg-orange-500/10 border border-orange-500/20 flex items-center justify-center shadow-xl">
                    <i data-lucide="wrench" class="w-8 h-8 text-orange-600"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 italic">{{ $repair->repair_number }}
                    </h1>
                    <div class="flex items-center gap-3 mt-1">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ $repair->device_brand }}
                            {{ $repair->device_model }}</span>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <span
                            class="px-3 py-1 bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 rounded-full text-[10px] font-black uppercase tracking-widest">{{ $repair->status->value }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('repair.edit', $repair) }}"
                    class="px-6 py-3 bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-white/5 transition-all shadow-sm flex items-center gap-2">
                    <i data-lucide="settings-2" class="w-4 h-4 text-orange-500"></i> Actualizar Estado
                </a>
                <button onclick="window.print()"
                    class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                    <i data-lucide="printer"
                        class="w-5 h-5 text-gray-400 group-hover:text-orange-500 transition-colors"></i>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Info -->
            <div class="space-y-8">
                <!-- Status Card -->
                <div
                    class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 p-8 space-y-6 shadow-2xl">
                    <h3
                        class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4"></i> Información de la Orden
                    </h3>

                    <div class="space-y-4">
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Ingreso</p>
                            <p class="text-sm font-bold mt-1 tracking-tight">
                                {{ optional($repair->received_at)->format('d M, Y - H:i') ?? 'N/A' }} hs</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5">
                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Prioridad</p>
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    class="w-2 h-2 rounded-full bg-{{ $repair->priority->value == 'urgent' ? 'red' : ($repair->priority->value == 'high' ? 'orange' : ($repair->priority->value == 'medium' ? 'blue' : 'zinc')) }}-500"></span>
                                <p class="text-sm font-black uppercase italic tracking-widest text-gray-900 dark:text-gray-100">
                                    {{ $repair->priority->value }}</p>
                            </div>
                        </div>
                        @if($repair->delivered_at)
                            <div class="p-4 rounded-2xl bg-emerald-500/5 border border-emerald-500/10">
                                <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Entregado</p>
                                <p class="text-sm font-bold text-emerald-700 mt-1 tracking-tight">
                                    {{ optional($repair->delivered_at)->format('d M, Y') ?? 'No entregado' }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Client Card -->
                <div class="bg-zinc-900 rounded-[40px] p-8 text-white space-y-6 shadow-2xl shadow-zinc-500/20">
                    <h3 class="text-[10px] font-black uppercase tracking-widest italic opacity-50">Cliente</h3>
                    <div class="flex items-center gap-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-white/10 flex items-center justify-center font-black text-xl italic uppercase">
                            {{ $repair->client->full_name[0] }}
                        </div>
                        <div>
                            <p class="text-lg font-black italic tracking-tight">{{ $repair->client->full_name }}</p>
                            <p class="text-xs font-bold opacity-50">{{ $repair->client->phone }}</p>
                        </div>
                    </div>
                    <div class="pt-6 border-t border-white/10 flex items-center justify-between">
                        <a href="{{ route('client.show', $repair->client_id) }}"
                            class="text-[10px] font-black uppercase tracking-widest hover:text-orange-400 transition-colors italic">Ver
                            perfil completo →</a>
                    </div>
                </div>
            </div>

            <!-- Right Details -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Issue & Diagnosis -->
                <div
                    class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 p-10 space-y-8 shadow-2xl">
                    <div class="space-y-4">
                        <h3
                            class="text-xs font-black text-gray-900 dark:text-gray-100 uppercase tracking-[0.2em] italic flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-5 h-5 text-orange-500"></i> Falla Reportada
                        </h3>
                        <div
                            class="p-6 rounded-3xl bg-gray-50 dark:bg-dark border border-dashed border-gray-200 dark:border-white/10">
                            <p class="text-gray-600 dark:text-gray-400 font-medium leading-relaxed italic">
                                "{{ $repair->reported_issue }}"</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h3
                            class="text-xs font-black text-gray-900 dark:text-gray-100 uppercase tracking-[0.2em] italic flex items-center gap-2">
                            <i data-lucide="terminal" class="w-5 h-5 text-blue-500"></i> Diagnóstico Técnico
                        </h3>
                        <div class="p-6 rounded-3xl bg-blue-500/5 border border-blue-500/10 min-h-[120px]">
                            @if($repair->diagnosis)
                                <p class="text-gray-700 dark:text-gray-300 font-bold leading-relaxed">{{ $repair->diagnosis }}
                                </p>
                            @else
                                <div
                                    class="flex items-center justify-center h-full text-gray-400 text-xs font-bold uppercase italic tracking-widest">
                                    Pendiente de diagnóstico
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="pt-8 border-t border-gray-100 dark:border-white/5 grid grid-cols-2 md:grid-cols-3 gap-6">
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Costo Estimado</p>
                            <p class="text-xl font-black text-gray-900 dark:text-gray-100 italic">USD
                                {{ number_format($repair->estimated_cost, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Seña / Adelanto</p>
                            <p class="text-xl font-black text-emerald-500 italic">USD
                                {{ number_format($repair->deposit_amount, 2) }}</p>
                        </div>
                        <div class="md:text-right">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Presupuesto Final</p>
                            <p class="text-2xl font-black text-orange-600 italic">USD
                                {{ number_format($repair->final_cost ?? $repair->estimated_cost, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Internal Notes -->
                @if($repair->internal_notes)
                    <div class="bg-gray-50 dark:bg-dark rounded-[40px] p-8 border border-gray-100 dark:border-white/5">
                        <h3
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic flex items-center gap-2 mb-4">
                            <i data-lucide="lock" class="w-4 h-4"></i> Notas Internas
                        </h3>
                        <p class="text-xs text-gray-500 font-medium leading-relaxed">{{ $repair->internal_notes }}</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
@endsection