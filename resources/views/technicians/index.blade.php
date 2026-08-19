@extends('layouts.admin')

@section('title', 'Técnicos')

@section('content')
    <div class="space-y-8 animate-in transition-all duration-500">

        <!-- Top Action Panel -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                    <i data-lucide="users-2" class="w-8 h-8 text-blue-500"></i> Staff / Equipo
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Gestioná el equipo de trabajo y sus permisos de acceso.</p>
            </div>
            <a href="{{ route('technician.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-2xl shadow-xl shadow-blue-500/20 text-sm font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-2 group">
                <i data-lucide="plus" class="w-4 h-4 group-hover:rotate-90 transition-all"></i>
                Nuevo Integrante
            </a>
        </div>

        <!-- Grid Layout for staff -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-20">
            @forelse($technicians as $tech)
                <div
                    class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none p-6 relative overflow-hidden group hover:-translate-y-2 transition-all duration-500">

                    <div
                        class="absolute right-0 top-0 w-24 h-24 bg-blue-500/5 rounded-bl-[60px] group-hover:bg-blue-500/10 transition-colors">
                    </div>

                    <div class="flex items-center gap-4 mb-6 relative">
                        <div
                            class="w-16 h-16 rounded-2xl bg-blue-50 dark:bg-dark flex items-center justify-center border border-blue-100 dark:border-white/10 shadow-sm relative overflow-hidden group-hover:scale-110 transition-transform">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($tech->full_name) }}&background=E0E7FF&color=3B82F6&bold=true"
                                class="w-full h-full object-cover" />
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-gray-100 tracking-tight italic">
                                {{ $tech->full_name }}
                            </h3>
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    class="w-2 h-2 rounded-full {{ $tech->is_active ? 'bg-emerald-500 animate-pulse' : 'bg-red-500' }}"></span>
                                <span
                                    class="text-[10px] font-black uppercase tracking-widest text-gray-400">{{ $tech->is_active ? 'Activo' : 'Inactivo' }}</span>
                                
                                <span class="mx-1 text-gray-300 dark:text-white/10">•</span>
                                
                                @if($tech->user_id)
                                    <span class="text-[10px] font-black uppercase tracking-widest text-emerald-500 flex items-center gap-1">
                                        <i data-lucide="shield-check" class="w-3 h-3"></i> Acceso OK
                                    </span>
                                @else
                                    <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 italic">Solo Registro</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3 text-xs font-bold text-gray-500">
                                <i data-lucide="phone" class="w-4 h-4 text-blue-500"></i>
                                {{ $tech->phone ?? 'Sin contacto' }}
                            </div>
                            <span class="px-3 py-1 bg-gray-100 dark:bg-dark border border-gray-200 dark:border-white/10 rounded-full text-[9px] font-black uppercase tracking-[0.15em] text-gray-500 italic">
                                {{ $tech->type ?? 'technician' }}
                            </span>
                        </div>
                        <div class="flex items-center gap-3 text-xs font-bold text-gray-500">
                            <i data-lucide="wrench" class="w-4 h-4 text-emerald-500"></i>
                            <span class="line-clamp-1 italic">{{ $tech->specialties ?? 'Multiservicio' }}</span>
                        </div>
                    </div>

                    <div
                        class="mt-8 pt-6 border-t border-gray-50 dark:border-white/5 flex items-center justify-between relative">
                        <div class="flex flex-col">
                            <span class="text-[10px] font-black uppercase text-gray-400">Reparaciones</span>
                            <span class="text-lg font-black text-gray-900 dark:text-gray-100 italic">0</span>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('technician.edit', $tech) }}"
                                class="p-3 bg-gray-50 dark:bg-dark hover:bg-blue-600 hover:text-white transition-all rounded-xl border border-transparent shadow-sm">
                                <i data-lucide="edit-3" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>

                </div>
            @empty
                <div
                    class="col-span-full py-20 bg-white dark:bg-dark-alt rounded-[40px] border border-dashed border-gray-200 dark:border-white/10 flex flex-col items-center justify-center text-gray-400">
                    <i data-lucide="user-x" class="w-16 h-16 mb-4 opacity-20"></i>
                    <p class="text-lg font-black italic">Aún no hay técnicos</p>
                    <a href="{{ route('technician.create') }}"
                        class="mt-4 text-blue-500 hover:underline text-xs font-black uppercase tracking-widest italic">Registrar
                        el primero</a>
                </div>
            @endforelse
        </div>

    </div>
@endsection