@extends('layouts.admin')

@section('title', 'Detalle de Negocio')

@section('content')
<div class="space-y-8 pb-20">

    {{-- Top Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('super-admin.organizations.index') }}" class="p-3 bg-white dark:bg-dark-alt rounded-2xl border border-gray-100 dark:border-white/5 hover:bg-gray-50 transition-all shadow-sm">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 italic">
                    Negocio: <span class="text-violet-500">{{ $organization->name }}</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight uppercase">
                    ID: {{ $organization->id }} • Plan actual: {{ $organization->plan }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <form action="{{ route('super-admin.organizations.toggle-status', $organization) }}" method="POST">
                @csrf
                <button type="submit" class="px-6 py-3 rounded-2xl shadow-xl text-xs font-black uppercase tracking-widest transition-all active:scale-95 {{ $organization->is_active ? 'bg-red-500 text-white' : 'bg-emerald-500 text-white' }}">
                    {{ $organization->is_active ? 'Desactivar Acceso' : 'Activar Acceso' }}
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left: Stats & Info --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Organization Stats --}}
            <div class="bg-white dark:bg-dark-alt p-8 rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none">
                <h3 class="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-8 italic">Métricas del Negocio</h3>
                <div class="space-y-6">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-black uppercase text-gray-400">Facturación Total</span>
                        <span class="text-lg font-black text-emerald-600 italic tracking-tighter">USD {{ number_format($stats['total_revenue'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-black uppercase text-gray-400">Ventas Realizadas</span>
                        <span class="text-lg font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">{{ $stats['total_sales'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-black uppercase text-gray-400">Reparaciones</span>
                        <span class="text-lg font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">{{ $stats['total_repairs'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-black uppercase text-gray-400">Stock Actual</span>
                        <span class="text-lg font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">{{ $stats['total_items'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Plan & Controls --}}
            <div class="bg-white dark:bg-dark-alt p-8 rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none">
                <h3 class="text-[10px] font-black uppercase text-gray-400 tracking-[0.2em] mb-6 italic">Gestión de Plan</h3>
                <form action="{{ route('super-admin.organizations.update-plan', $organization) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="space-y-2">
                        <label class="text-[10px] font-bold uppercase text-gray-400 ml-2">Seleccionar Nuevo Plan</label>
                        <select name="plan" class="w-full px-4 py-3 rounded-2xl bg-gray-50 dark:bg-dark border-none outline-none focus:ring-2 ring-violet-500/20 text-sm font-bold">
                            @foreach(['free' => 'Free', 'starter' => 'Starter', 'pro' => 'Pro', 'enterprise' => 'Enterprise'] as $val => $label)
                                <option value="{{ $val }}" {{ $organization->plan == $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-4 bg-violet-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:scale-105 transition-all">Actualizar Plan</button>
                </form>
            </div>
        </div>

        {{-- Right: Users & Branches --}}
        <div class="lg:col-span-2 space-y-6">
             {{-- Users List --}}
             <div class="bg-white dark:bg-dark-alt p-8 rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2">
                        <i data-lucide="users" class="w-5 h-5 text-violet-500"></i> Usuarios del Negocio
                    </h3>
                </div>
                <div class="space-y-3">
                    @foreach($organization->users as $user)
                    <div class="flex items-center justify-between p-4 rounded-3xl bg-gray-50/50 dark:bg-dark/50 border border-transparent">
                        <div class="flex items-center gap-4">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=E5E7EB&color=6B7280" class="w-10 h-10 rounded-xl" />
                            <div>
                                <p class="text-sm font-black text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div>
                             <span class="text-[9px] px-3 py-1 bg-gray-100 dark:bg-white/5 text-gray-500 dark:text-gray-400 rounded-full font-black uppercase tracking-widest">
                                {{ $user->role }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Branches List --}}
            <div class="bg-white dark:bg-dark-alt p-8 rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/50 dark:shadow-none">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-5 h-5 text-emerald-500"></i> Sucursales / Locales
                    </h3>
                </div>
                @if($organization->branches->isEmpty())
                    <p class="text-xs text-center text-gray-400 py-10">Este negocio no tiene sucursales registradas aún.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($organization->branches as $branch)
                        <div class="p-4 rounded-3xl bg-gray-50/50 dark:bg-dark/50 border border-transparent">
                            <p class="text-sm font-black text-gray-900 dark:text-gray-100 flex items-center gap-2">
                                <i data-lucide="building" class="w-4 h-4 text-emerald-500"></i> {{ $branch->name }}
                                @if($branch->is_main) <span class="text-[8px] bg-emerald-500/10 text-emerald-500 px-2 py-0.5 rounded-full uppercase">Principal</span> @endif
                            </p>
                            <p class="text-[10px] text-gray-400 font-bold mt-1">{{ $branch->address ?: 'Sin dirección' }}</p>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
