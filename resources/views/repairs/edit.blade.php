@extends('layouts.admin')

@section('title', 'Actualizar Reparación')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500 pb-20">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="settings-2" class="w-8 h-8 text-orange-500"></i> Actualizar Reparación
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">
                    {{ $repair->repair_number }} - {{ $repair->device_brand }} {{ $repair->device_model }}
                </p>
            </div>
            <a href="{{ route('repair.show', $repair) }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <form action="{{ route('repair.update', $repair) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-orange-500/5 dark:shadow-none p-8 md:p-10 space-y-6">
                        <h2
                            class="text-xs font-black text-orange-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="clipboard-check" class="w-4 h-4"></i> Estado del Servicio
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Estado</label>
                                <select name="status" required
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                    @php
                                        $statuses = [
                                            'pending' => 'Pendiente',
                                            'in_progress' => 'En Proceso',
                                            'ready' => 'Lista para Entrega',
                                            'delivered' => 'Entregada',
                                            'cancelled' => 'Cancelada',
                                        ];
                                    @endphp
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $repair->status->value) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Técnico
                                    asignado</label>
                                <select name="technician_id"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                    <option value="">Sin asignar</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}"
                                            {{ (string) old('technician_id', $repair->technician_id) === (string) $tech->id ? 'selected' : '' }}>
                                            {{ $tech->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-orange-500/5 dark:shadow-none p-8 md:p-10 space-y-6">
                        <h2
                            class="text-xs font-black text-orange-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="stethoscope" class="w-4 h-4"></i> Diagnóstico y Notas
                        </h2>

                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Diagnóstico
                                técnico</label>
                            <textarea name="diagnosis" rows="5"
                                class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="Detalle del diagnóstico y trabajo realizado...">{{ old('diagnosis', $repair->diagnosis) }}</textarea>
                        </div>

                        <div class="space-y-2">
                            <label
                                class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Notas
                                internas</label>
                            <textarea name="internal_notes" rows="4"
                                class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                placeholder="Notas internas para el equipo técnico...">{{ old('internal_notes', $repair->internal_notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-orange-500/5 dark:shadow-none p-8 md:p-10 space-y-6">
                        <h2
                            class="text-xs font-black text-orange-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="badge-dollar-sign" class="w-4 h-4"></i> Cierre y Garantía
                        </h2>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Costo
                                    final</label>
                                <input type="number" step="0.01" min="0" name="final_cost"
                                    value="{{ old('final_cost', $repair->final_cost) }}"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="0.00">
                            </div>

                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Garantía
                                    (días)</label>
                                <input type="number" min="0" name="warranty_days"
                                    value="{{ old('warranty_days', $repair->warranty_days) }}"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="Ej: 30">
                            </div>

                            <div class="p-4 rounded-2xl bg-orange-500/5 border border-orange-500/10">
                                <p class="text-[10px] font-black text-orange-500 uppercase tracking-widest">Falla reportada</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 font-medium">
                                    {{ $repair->reported_issue }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <button type="submit"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-black py-5 rounded-3xl shadow-2xl shadow-orange-500/30 active:scale-95 transition-all text-xs uppercase tracking-[0.2em] flex items-center justify-center gap-3 italic">
                            <i data-lucide="save" class="w-5 h-5"></i>
                            Guardar Cambios
                        </button>
                        <a href="{{ route('repair.show', $repair) }}"
                            class="block w-full text-center py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-orange-500 transition-colors italic">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
