@extends('layouts.admin')

@section('title', 'Historial de Notificaciones')

@section('content')
    <div class="space-y-6 md:space-y-8 page-transition">

        <!-- Welcome Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight dark:text-gray-100 flex items-center gap-2">
                    <span class="text-violet-gradient tracking-tighter">Historial de</span> Notificaciones 🔔
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Revisá todas las alertas y notificaciones del sistema.</p>
            </div>
            <div class="flex items-center gap-2">
                @if(auth()->user()->unreadNotifications->count() > 0)
                    <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="bg-dark hover:bg-dark-alt dark:bg-dark-alt dark:hover:bg-dark text-white px-5 py-2.5 rounded-2xl shadow-xl text-sm font-bold flex items-center gap-2 transition-all active:scale-95 group">
                            <i data-lucide="check-check" class="w-4 h-4"></i>
                            Marcar todo como leído
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-dark-alt rounded-[40px] border border-gray-100 dark:border-white/5 shadow-2xl relative overflow-hidden">
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5">
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Notificación</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Detalles</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400">Fecha</th>
                            <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                        @forelse($notifications as $item)
                            <tr class="group hover:bg-gray-50/50 dark:hover:bg-white/5 transition-all {{ $item->read_at ? 'opacity-60' : '' }}">
                                <td class="px-8 py-6 text-sm font-bold">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner {{ $item->read_at ? 'bg-gray-100 text-gray-400' : 'bg-primary/10 text-primary' }}">
                                            <i data-lucide="{{ $item->data['icon'] ?? 'bell' }}" class="w-5 h-5"></i>
                                        </div>
                                        <div>
                                            <p class="text-gray-900 dark:text-gray-100 uppercase tracking-tighter italic">
                                                {{ $item->data['title'] ?? 'Notificación' }}
                                            </p>
                                            @if(!$item->read_at)
                                                <span class="inline-block px-2 py-0.5 bg-red-500/10 text-red-500 text-[8px] font-black uppercase tracking-widest rounded-md mt-1 animate-pulse">Nueva</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                                        {{ $item->data['message'] ?? '' }}
                                    </p>
                                </td>
                                <td class="px-8 py-6 text-sm font-black text-gray-400">
                                    {{ $item->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-8 py-6 text-right space-x-2">
                                    <div class="flex items-center justify-end gap-2">
                                        @if(!$item->read_at)
                                            <form action="{{ route('notifications.mark-as-read', $item->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="p-2 hover:bg-emerald-500/10 text-gray-400 hover:text-emerald-500 rounded-xl transition-all" title="Marcar como leída">
                                                    <i data-lucide="check" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('notifications.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 hover:bg-red-500/10 text-gray-400 hover:text-red-500 rounded-xl transition-all" title="Eliminar">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-16 text-center italic text-gray-400 font-bold uppercase tracking-widest opacity-50">
                                    No hay notificaciones para mostrar
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($notifications->hasPages())
                <div class="px-8 py-6 border-t border-gray-100 dark:border-white/5 bg-gray-50/30 dark:bg-white/5">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
