<header
    class="flex h-16 w-full items-center justify-between border-b border-gray-100 dark:border-white/5 bg-white/80 dark:bg-dark-alt/80 backdrop-blur-md px-4 md:px-8 sticky top-0 z-50 transition-all duration-300 shadow-sm border-l-0">

    <div class="flex items-center gap-4">
        <button @click="sidebarOpen = !sidebarOpen"
            class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl transition-colors">
            <i data-lucide="menu-left" class="w-6 h-6" x-show="sidebarOpen"></i>
            <i data-lucide="menu" class="w-6 h-6" x-show="!sidebarOpen"></i>
        </button>

        <!-- Breadcrumbs -->
        <nav class="hidden md:flex items-center text-sm font-medium text-gray-400 space-x-2">
            <a href="{{ route('dashboard') }}" class="hover:text-primary transition-colors flex items-center gap-1">
                <i data-lucide="home" class="w-4 h-4"></i> {{ config('platform.brand.name') }}
            </a>
            <i data-lucide="chevron-right" class="w-4 h-4"></i>
            <span class="text-gray-900 dark:text-gray-100 font-bold capitalize mr-2">@yield('title', 'Dashboard')</span>
            
            @if(Auth::check() && Auth::user()->is_demo)
                <span class="px-2 py-0.5 bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-[10px] font-black uppercase tracking-widest rounded-lg animate-pulse">
                    Modo Demo
                </span>
            @else
                <span class="px-2 py-0.5 bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[10px] font-black uppercase tracking-widest rounded-lg">
                    Modo Real (Producción)
                </span>
            @endif
        </nav>
    </div>

    <!-- Right Actions -->
    <div class="flex items-center space-x-1 sm:space-x-2 md:space-x-4">

        <!-- Global Search Trigger -->
        <div @click="$dispatch('open-search')"
            class="hidden lg:flex items-center bg-gray-100 dark:bg-dark px-4 py-2 rounded-xl border border-transparent hover:border-primary/30 hover:bg-white dark:hover:bg-dark-alt transition-all duration-300 w-64 shadow-inner cursor-pointer group">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 mr-2 group-hover:text-primary transition-colors"></i>
            <span class="text-sm font-medium text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">Buscar (⌘K)</span>
            <div class="ml-auto flex items-center gap-1 opacity-50 group-hover:opacity-100 transition-opacity">
                <span class="text-[10px] bg-gray-200 dark:bg-white/5 py-0.5 px-1.5 rounded-lg text-gray-400 font-bold tracking-tighter border border-gray-100 dark:border-white/5 shadow-sm">⌘K</span>
            </div>
        </div>

        <!-- Barcode Scanner Trigger for Mobile & Desktop -->
        <div x-data="barcodeScanner()">
            <button type="button" @click="toggleCamera()"
                class="flex items-center gap-1.5 px-3 py-2 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-xl font-bold text-xs transition-all shadow-sm"
                title="Escanear Código de Barras con Cámara">
                <i data-lucide="camera" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Escanear</span>
            </button>

            <!-- Camera Modal -->
            <div x-show="cameraOpen" x-cloak @click.self="closeCamera()"
                class="fixed inset-0 z-[200] bg-black/80 flex items-center justify-center p-4">
                <div class="bg-white dark:bg-dark-alt rounded-3xl p-6 max-w-md w-full space-y-4 shadow-2xl">
                    <div class="flex items-center justify-between">
                        <h4 class="font-black text-sm uppercase tracking-wider text-gray-900 dark:text-white flex items-center gap-2">
                            <i data-lucide="scan-barcode" class="w-5 h-5 text-primary"></i>
                            Escanear con Cámara
                        </h4>
                        <button type="button" @click="closeCamera()" class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <video x-ref="video" class="w-full rounded-2xl bg-black aspect-video object-cover border border-white/10" autoplay muted playsinline></video>
                    <p class="text-xs text-gray-400 text-center font-semibold">Apuntá la cámara al código de barras del producto</p>
                    <button type="button" @click="closeCamera()"
                        class="w-full py-3 rounded-2xl bg-gray-100 dark:bg-dark text-gray-500 font-bold uppercase text-xs tracking-wider">
                        Cerrar Cámara
                    </button>
                </div>
            </div>
        </div>

        <div class="flex items-center h-8 w-px bg-gray-100 dark:bg-white/5 mx-1"></div>

        <!-- Theme Toggle -->
        <button @click="isDarkMode = !isDarkMode"
            class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl transition-colors relative group"
            title="Cambiar Tema">
            <i data-lucide="sun" class="w-5 h-5 group-hover:rotate-45 transition-transform" x-show="isDarkMode"
                x-cloak></i>
            <i data-lucide="moon" class="w-5 h-5 group-hover:-rotate-12 transition-transform" x-show="!isDarkMode"
                x-cloak></i>
        </button>

        <!-- Notifications -->
        <div x-data="{ 
            open: false,
            notifications: @js(Auth::user()->unreadNotifications->take(8)),
            count: {{ Auth::user()->unreadNotifications->count() }},
            init() {
                // Polling every 30 seconds for Hostinger compatibility
                setInterval(() => {
                    this.checkNew();
                }, 30000);
            },
            async checkNew() {
                try {
                    const res = await fetch('{{ route('notifications.check') }}');
                    const data = await res.json();
                    
                    if (data.count > this.count) {
                        // New notification arrived!
                        const latest = data.unread[0];
                        window.toast(latest.data.message, 'info', latest.data.title);
                        // Update UI
                        this.notifications = data.unread;
                        this.count = data.count;
                        this.$nextTick(() => lucide.createIcons());
                    }
                    
                    // Always sync count in case some were read in another tab
                    this.count = data.count;
                    this.notifications = data.unread;
                } catch (e) {
                    console.error('Polling error:', e);
                }
            },
            async markAllAsRead() {
                try {
                    await fetch('{{ route('notifications.mark-all-read') }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    this.notifications = [];
                    this.count = 0;
                } catch (e) { console.error(e); }
            },
            async markAsRead(id) {
                try {
                    await fetch('{{ route('notifications.mark-as-read', '') }}/' + id, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    });
                    this.notifications = this.notifications.filter(n => n.id !== id);
                    this.count--;
                } catch (e) { console.error(e); }
            }
        }" x-init="init()" class="relative">
            <button @click="open = !open"
                class="p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl transition-colors relative group">
                <i data-lucide="bell-ring" class="w-5 h-5 group-hover:shake transition-all text-orange-500"></i>
                <template x-if="count > 0">
                    <span class="absolute top-2 right-2.5 w-4 h-4 bg-red-500 border-2 border-white dark:border-dark-alt rounded-full flex items-center justify-center text-[8px] text-white font-black animate-pulse" x-text="count"></span>
                </template>
            </button>
            
            <!-- Dropdown -->
            <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="fixed md:absolute right-4 left-4 md:left-auto md:right-0 mt-3 md:w-96 bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 rounded-[40px] shadow-2xl overflow-hidden z-[60]">
                
                <div class="px-8 py-6 border-b border-gray-50 dark:border-white/5 flex items-center justify-between bg-gray-50/50 dark:bg-white/5">
                    <div class="flex flex-col gap-0.5">
                        <h3 class="font-black italic text-[11px] uppercase tracking-[0.2em] text-gray-900 dark:text-gray-100">Centro de Mensajes</h3>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest" x-text="count + ' pendientes'"></p>
                    </div>
                    <template x-if="count > 0">
                        <button @click="markAllAsRead" class="text-[9px] font-black uppercase tracking-widest text-primary hover:bg-primary/10 px-3 py-1.5 rounded-xl transition-all border border-primary/20">
                            Limpiar todo
                        </button>
                    </template>
                </div>

                <div class="max-h-[450px] overflow-y-auto custom-scrollbar">
                    <template x-if="count === 0">
                        <div class="py-16 flex flex-col items-center text-gray-400 text-center px-10">
                            <div class="w-16 h-16 bg-gray-100 dark:bg-white/5 rounded-full flex items-center justify-center mb-4 animate-bounce">
                                <i data-lucide="bell-off" class="w-8 h-8 opacity-20"></i>
                            </div>
                            <p class="font-black italic text-sm text-gray-900 dark:text-gray-100 uppercase tracking-widest">¡Todo al día!</p>
                            <p class="text-[10px] font-bold uppercase tracking-widest mt-2 opacity-50">No tenés notificaciones sin leer</p>
                        </div>
                    </template>

                    <template x-for="item in notifications" :key="item.id">
                        <div class="relative block px-8 py-6 hover:bg-gray-50/80 dark:hover:bg-white/5 border-b border-gray-50 dark:border-white/5 transition-all group">
                            <div class="flex gap-5">
                                <div class="w-12 h-12 rounded-2xl flex-shrink-0 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner"
                                     :class="{
                                        'bg-emerald-500/10 text-emerald-500': item.data.type === 'sale_created',
                                        'bg-violet-500/10 text-violet-500': item.data.type === 'repair_completed',
                                        'bg-amber-500/10 text-amber-500': item.data.type === 'low_stock',
                                        'bg-primary/10 text-primary': !item.data.type
                                     }">
                                    <i :data-lucide="item.data.icon || 'star'" class="w-6 h-6"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start mb-1.5">
                                        <p class="text-xs font-black text-gray-900 dark:text-gray-100 uppercase tracking-tighter italic" x-text="item.data.title"></p>
                                        <button @click.stop="markAsRead(item.id)" class="p-1 px-2 hover:bg-white dark:hover:bg-dark-alt rounded-lg text-gray-400 hover:text-primary transition-all shadow-sm border border-transparent hover:border-primary/20" title="Marcar como leída">
                                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                    <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 leading-tight mb-2" x-text="item.data.message"></p>
                                    <div class="flex items-center justify-between mt-auto">
                                        <span class="text-[8px] font-black uppercase tracking-widest text-gray-400 bg-gray-100 dark:bg-white/5 px-2 py-0.5 rounded-md" x-text="item.data.seller_name || item.data.technician_name || 'Mito Admin'"></span>
                                        <a :href="item.data.url || '#'" class="text-[9px] font-black text-primary uppercase tracking-widest hover:underline italic">Detalles</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <a href="{{ route('notification.index') }}" class="block py-5 text-center bg-gray-50/80 dark:bg-white/5 hover:bg-primary hover:text-white transition-all text-[11px] font-black uppercase tracking-[0.3em] italic text-gray-500">
                    Ver Historial Completo
                </a>
            </div>
        </div>

        <!-- User Dropdown (Optional if Sidebar Profile is enough) -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="flex items-center gap-2 p-1 pl-1 sm:pl-3 bg-white dark:bg-dark border border-gray-100 dark:border-white/5 rounded-full shadow-sm hover:shadow-md transition-all active:scale-95">
                <span
                    class="hidden sm:inline text-xs font-bold text-gray-700 dark:text-gray-300 max-w-[140px] truncate">{{ Auth::user()->organization?->name ?? 'Admin SaaS' }}</span>
                <i data-lucide="chevron-down" class="hidden sm:block w-3.5 h-3.5 text-gray-400 transition-transform"
                    :class="{ 'rotate-180': open }"></i>
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->organization?->name ?? 'A') }}&background=E5E7EB&color=6B7280"
                    class="w-6 h-6 rounded-full" />
            </button>
            <!-- Organization Selector -->
            <div x-show="open" @click.away="open = false" x-transition
                class="absolute right-0 mt-3 w-56 bg-white dark:bg-dark-alt border border-gray-100 dark:border-white/5 rounded-2xl shadow-2xl p-2 z-50">
                @if(Auth::user()->hasRole([\App\Enums\UserRole::OWNER, \App\Enums\UserRole::MANAGER]) && config('platform.mode') !== 'single_license')
                <div class="px-4 py-3 bg-gray-50 dark:bg-white/5 rounded-xl mb-1">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Suscripción Activa</p>
                    <p class="text-xs font-bold text-primary">{{ Auth::user()->organization?->subscriptionPlan?->name ?? 'Plan Gratuito' }}</p>
                </div>
                @endif
                @if(Auth::user()->hasRole([\App\Enums\UserRole::OWNER, \App\Enums\UserRole::MANAGER]))
                <a href="{{ route('organization.settings') }}"
                    class="flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5 rounded-xl">
                    <i data-lucide="building-2" class="w-4 h-4 mr-3"></i> Mi Negocio
                </a>
                @endif
            </div>
        </div>

    </div>
</header>

<style>
    @keyframes shake {

        0%,
        100% {
            transform: rotate(0);
        }

        25% {
            transform: rotate(10deg);
        }

        75% {
            transform: rotate(-10deg);
        }
    }

    .group-hover\:shake:hover {
        animation: shake 0.3s ease-in-out infinite;
    }
</style>