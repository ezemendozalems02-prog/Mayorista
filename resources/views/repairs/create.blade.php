@extends('layouts.admin')

@section('title', 'Nueva Reparación')

@section('content')
    <div class="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-500 pb-20" x-data="{ showQuickClientModal: false }" @close-quick-client-repair.window="showQuickClientModal = false">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="wrench" class="w-8 h-8 text-orange-500"></i> Orden de Servicio
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight italic">Ingresá los detalles
                    del equipo y la falla reportada por el cliente.</p>
            </div>
            <a href="{{ route('repair.index') }}"
                class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 dark:hover:bg-white/5 transition-all group shadow-sm">
                <i data-lucide="x" class="w-5 h-5 text-gray-400 group-hover:rotate-90 transition-transform"></i>
            </a>
        </div>

        <!-- Form -->
        <form action="{{ route('repair.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Main Content Column -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- Client & Device -->
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-orange-500/5 dark:shadow-none p-8 md:p-10 space-y-8">
                        <h2
                            class="text-xs font-black text-orange-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="user" class="w-4 h-4"></i> Cliente y Dispositivo
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Client Select -->
                            <div class="md:col-span-2 space-y-2">
                                <div class="flex items-center justify-between ml-1">
                                    <label
                                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest italic">Cliente
                                        <span class="text-red-500">*</span></label>
                                    <button type="button" @click="showQuickClientModal = true"
                                        class="text-[10px] font-black text-orange-500 uppercase hover:underline">Nuevo cliente</button>
                                </div>
                                <select id="repair-client-select" name="client_id" required data-placeholder="Buscar cliente..."
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                    <option value="">Seleccionar Cliente...</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->full_name }}
                                            ({{ $client->phone ?? 'S/T' }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Brand -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Marca
                                    del Equipo <span class="text-red-500">*</span></label>
                                <input type="text" name="device_brand" value="Apple" required
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="Ej: Apple">
                            </div>

                            <!-- Model -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Modelo
                                    Exacto <span class="text-red-500">*</span></label>
                                <input type="text" name="device_model" value="{{ old('device_model') }}" required
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="Ej: iPhone 13 Pro">
                            </div>

                            <!-- IMEI/Serial -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">IMEI</label>
                                <input type="text" name="imei"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="Opcional">
                            </div>
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Nro
                                    de Serie</label>
                                <input type="text" name="serial_number"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="Opcional">
                            </div>
                        </div>
                    </div>

                    <!-- Issue Details -->
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-orange-500/5 dark:shadow-none p-8 md:p-10 space-y-6">
                        <h2
                            class="text-xs font-black text-orange-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="clipboard-list" class="w-4 h-4"></i> Problema a Resolver
                        </h2>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Falla
                                    Reportada <span class="text-red-500">*</span></label>
                                <textarea name="reported_issue" rows="4" required
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                    placeholder="Ej: No enciende después de caída, pantalla rota, no carga..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info Column -->
                <div class="space-y-8">
                    <!-- Planning & Cost -->
                    <div
                        class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-orange-500/5 dark:shadow-none p-8 md:p-10 space-y-6">
                        <h2
                            class="text-xs font-black text-orange-500 uppercase tracking-[0.3em] flex items-center gap-2 italic">
                            <i data-lucide="activity" class="w-4 h-4"></i> Gestión y Costos
                        </h2>

                        <div class="space-y-6">
                            <!-- Priority -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Prioridad</label>
                                <select name="priority"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none">
                                    <option value="medium">Media</option>
                                    <option value="low">Baja</option>
                                    <option value="high">Alta</option>
                                    <option value="urgent">Urgente</option>
                                </select>
                            </div>

                            <!-- Technician -->
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Asignar
                                    Técnico</label>
                                <select name="technician_id"
                                    class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold appearance-none italic">
                                    <option value="">Por asignar...</option>
                                    @foreach($technicians as $tech)
                                        <option value="{{ $tech->id }}">{{ $tech->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="border-t border-gray-100 dark:border-white/5 pt-6 space-y-6">
                                <!-- Estimated Cost -->
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Presupuesto
                                        Estimado</label>
                                    <input type="number" step="0.01" name="estimated_cost"
                                        class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 focus:bg-white dark:focus:bg-dark-alt outline-none transition-all duration-300 font-bold"
                                        placeholder="0.00">
                                </div>

                                <!-- Deposit -->
                                <div class="space-y-2">
                                    <label
                                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest ml-1 italic">Adelanto
                                        Recibido</label>
                                    <input type="number" step="0.01" name="deposit_amount"
                                        class="w-full px-6 py-4 rounded-2xl bg-gray-100 dark:bg-dark border border-transparent font-bold text-orange-500"
                                        placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="space-y-4">
                        <button type="submit"
                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-black py-5 rounded-3xl shadow-2xl shadow-orange-500/30 active:scale-95 transition-all text-xs uppercase tracking-[0.2em] flex items-center justify-center gap-3 italic">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                            Generar Ticket
                        </button>
                        <a href="{{ route('repair.index') }}"
                            class="block w-full text-center py-4 text-xs font-black text-gray-400 uppercase tracking-widest hover:text-orange-500 transition-colors italic">Descartar
                            Orden</a>
                    </div>
                </div>

            </div>
        </form>

        <!-- Quick Client Modal -->
        <div x-show="showQuickClientModal" x-cloak
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-dark/60 backdrop-blur-sm animate-in fade-in duration-300">
            <div @click.away="showQuickClientModal = false"
                class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl w-full max-w-md p-10 space-y-6 animate-in zoom-in duration-300">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-black italic tracking-tight">Alta rápida de cliente</h3>
                    <button type="button" @click="showQuickClientModal = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>

                <form id="repair-quick-client-form" class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Nombre y apellido</label>
                        <input type="text" name="full_name" required
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 outline-none font-bold"
                            placeholder="Ej: Juan Perez">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Email</label>
                        <input type="email" name="email" required
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 outline-none font-bold"
                            placeholder="cliente@email.com">
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Teléfono</label>
                        <input type="text" name="phone"
                            class="w-full px-6 py-4 rounded-2xl bg-gray-50 dark:bg-dark border border-transparent focus:border-orange-500/50 outline-none font-bold"
                            placeholder="+54 9 11 ...">
                    </div>
                    <button type="submit"
                        class="w-full bg-orange-600 text-white font-black py-4 rounded-2xl shadow-xl hover:bg-orange-700 transition-all uppercase text-xs tracking-widest italic">
                        Guardar cliente y continuar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const quickClientForm = document.getElementById('repair-quick-client-form');
            const clientSelect = document.getElementById('repair-client-select');
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
                    const optionLabel = `${client.full_name} (${client.phone || 'S/T'})`;
                    const tom = clientSelect.tomselect;

                    if (tom) {
                        tom.addOption({ value: client.id, text: optionLabel });
                        tom.setValue(String(client.id));
                    } else {
                        const newOption = new Option(optionLabel, client.id, true, true);
                        clientSelect.add(newOption);
                    }

                    quickClientForm.reset();
                    window.dispatchEvent(new CustomEvent('close-quick-client-repair'));
                } catch (error) {
                    alert('No se pudo crear el cliente. Revisa los datos e inténtalo nuevamente.');
                }
            });
        });
    </script>
@endsection