@extends('layouts.admin')

@section('title', 'Master Control Panel')

@section('content')
<div class="space-y-8 pb-20">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black tracking-tight dark:text-gray-100 flex items-center gap-3 italic">
                <i data-lucide="shield-check" class="w-8 h-8 text-red-500"></i> Mito Master
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium tracking-tight">
                Control global de la infraestructura, negocios y operaciones.
            </p>
        </div>
        <div class="flex items-center gap-3">
             <div class="px-4 py-2 bg-red-500/10 border border-red-500/20 rounded-2xl">
                <span class="text-xs font-black text-red-500 uppercase tracking-widest">ACCESS: SUPER_ADMIN</span>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-dark-alt p-8 rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/5 dark:shadow-none relative overflow-hidden group">
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic mb-1">Negocios Registrados</h3>
            <p class="text-4xl font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">{{ $stats['total_organizations'] }}</p>
            <p class="mt-4 text-[10px] font-bold text-emerald-500 uppercase flex items-center gap-1">
                <i data-lucide="check-circle" class="w-3 h-3"></i> {{ $stats['active_organizations'] }} activos
            </p>
            <i data-lucide="building-2" class="absolute -right-4 -bottom-4 w-24 h-24 text-gray-500/5 group-hover:scale-110 transition-transform"></i>
        </div>

        <div class="bg-violet-600 p-8 rounded-[40px] text-white shadow-2xl shadow-violet-500/20 relative overflow-hidden group">
            <h3 class="text-[10px] font-black uppercase tracking-widest italic opacity-60 mb-1">Facturación Global</h3>
            <p class="text-3xl font-black italic tracking-tighter leading-none">USD {{ number_format($stats['total_revenue'], 2, ',', '.') }}</p>
            <p class="mt-4 text-[10px] font-bold opacity-60 uppercase">{{ number_format($stats['total_sales'], 0, ',', '.') }} ventas totales</p>
            <i data-lucide="dollar-sign" class="absolute -right-4 -bottom-4 w-24 h-24 text-white/10 group-hover:rotate-12 transition-transform"></i>
        </div>

        <div class="bg-white dark:bg-dark-alt p-8 rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/5 dark:shadow-none relative overflow-hidden group">
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic mb-1">Usuarios Totales</h3>
            <p class="text-4xl font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">{{ $stats['total_users'] }}</p>
            <p class="mt-4 text-[10px] font-bold text-gray-400 uppercase">En toda la plataforma</p>
            <i data-lucide="users" class="absolute -right-4 -bottom-4 w-24 h-24 text-blue-500/5 group-hover:-rotate-12 transition-transform"></i>
        </div>

        <div class="bg-white dark:bg-dark-alt p-8 rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/5 dark:shadow-none relative overflow-hidden group">
            <h3 class="text-[10px] font-black text-gray-400 uppercase tracking-widest italic mb-1">Reparaciones Globales</h3>
            <p class="text-4xl font-black text-gray-900 dark:text-gray-100 italic tracking-tighter">{{ $stats['total_repairs'] }}</p>
            <p class="mt-4 text-[10px] font-bold text-gray-400 uppercase">Servicio técnico global</p>
            <i data-lucide="wrench" class="absolute -right-4 -bottom-4 w-24 h-24 text-amber-500/5 group-hover:scale-110 transition-transform"></i>
        </div>
    </div>

    {{-- Tables Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Latest Organizations --}}
        <div class="bg-white dark:bg-dark-alt p-8 rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/5 dark:shadow-none">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2">
                    <i data-lucide="building" class="w-5 h-5 text-red-500"></i> Últimos Negocios
                </h3>
                <a href="{{ route('super-admin.organizations.index') }}" class="text-[10px] font-black text-violet-500 uppercase tracking-widest hover:underline">Ver todos</a>
            </div>
            <div class="space-y-4">
                @foreach($latest_organizations as $org)
                <div class="flex items-center justify-between p-4 rounded-3xl bg-gray-50/50 dark:bg-dark/50 border border-transparent hover:border-red-500/20 transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white dark:bg-dark-alt flex items-center justify-center border border-gray-100 dark:border-white/5 font-black text-xs">
                            {{ substr($org->name, 0, 2) }}
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-900 dark:text-gray-100 italic tracking-tight">{{ $org->name }}</p>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $org->plan }} • {{ $org->users_count }} usuarios</p>
                        </div>
                    </div>
                    <div class="text-right">
                         <span class="text-[9px] px-3 py-1 rounded-full font-black uppercase tracking-widest {{ $org->is_active ? 'bg-emerald-500/10 text-emerald-500' : 'bg-red-500/10 text-red-600' }}">
                            {{ $org->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Top Organizations by Revenue --}}
        <div class="bg-white dark:bg-dark-alt p-8 rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl shadow-gray-200/5 dark:shadow-none">
            <h3 class="text-sm font-black text-gray-900 dark:text-gray-100 uppercase tracking-widest italic flex items-center gap-2 mb-8">
                <i data-lucide="trending-up" class="w-5 h-5 text-emerald-500"></i> Top Facturación
            </h3>
            <div class="space-y-6">
                @foreach($revenue_by_org as $org)
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-black dark:text-gray-100 italic">{{ $org->name }}</span>
                        <span class="text-xs font-black text-emerald-600">USD {{ number_format($org->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-white/5 h-2 rounded-full overflow-hidden">
                        @php $pct = ($stats['total_revenue'] > 0) ? ($org->total / $stats['total_revenue'] * 100) : 0; @endphp
                        <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
