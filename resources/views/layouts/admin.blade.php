<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('platform.brand.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset(config('platform.brand.favicon')) }}">

    <script>
        // Init theme immediately to avoid flash. Default to dark mode.
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            document.documentElement.classList.add('dark');
        }
    </script>

    <!-- Tailwind CSS (No NPM) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#a855f7', // violet-500
                        secondary: '#7c3aed', // violet-600
                        dark: '#0F0F12',
                        'dark-alt': '#1F1F23',
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js (Interactivity) -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <!-- Inter Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .sidebar-item-active {
            background: linear-gradient(to right, rgba(168, 85, 247, 0.1), rgba(168, 85, 247, 0.02));
            border-left: 3px solid #a855f7;
            color: #a855f7;
        }

        .glass-dark {
            background: rgba(15, 15, 18, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .text-violet-gradient,
        .text-silver-gradient {
            -webkit-text-fill-color: transparent;
            background: linear-gradient(90deg, #7c3aed, #a855f7, #c084fc);
            -webkit-background-clip: text;
            background-clip: text;
        }

        .btn-violet {
            color: #fff;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            transition: all .3s;
        }

        .btn-violet:hover {
            box-shadow: 0 10px 15px -3px rgba(124, 58, 237, 0.3);
            filter: brightness(1.1);
            transform: translateY(-1px);
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        /* Hide scrollbar for IE, Edge and Firefox */
        .scrollbar-hide {
            -ms-overflow-style: none;
            /* IE and Edge */
            scrollbar-width: none;
            /* Firefox */
        }

        /* Animated transition for charts or panels */
        .page-transition {
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-8px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        /* Evitar zoom automático en móviles (iOS) al enfocar inputs/selects */
        @media screen and (max-width: 1024px) {
            input, 
            select, 
            textarea, 
            .ts-control, 
            .ts-input, 
            .ts-control input {
                font-size: 16px !important;
            }
        }
    </style>
</head>

<body class="h-full bg-[#F5F5FA] dark:bg-dark text-gray-900 dark:text-gray-100 overflow-hidden" x-data="{ 
        sidebarOpen: window.innerWidth > 1024, 
        isDarkMode: document.documentElement.classList.contains('dark'),
        dolarRate: null,
        fetchDolar() {
            fetch('https://dolarapi.com/v1/dolares/blue')
                .then(res => res.json())
                .then(data => this.dolarRate = data.venta)
                .catch(err => console.error('Error fetching dolar:', err));
        }
    }" x-init="
        fetchDolar();
        $watch('isDarkMode', val => {
            if (val) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        });

        // Toast dispatcher
        window.toast = (message, type = 'success', title = '') => {
            window.dispatchEvent(new CustomEvent('vortex-toast', { 
                detail: { message, type, title } 
            }));
        };

        // Flash Messages to Toasts
        @if(session('success'))
            setTimeout(() => window.toast('{{ session('success') }}', 'success', '¡Éxito!'), 500);
        @endif
        @if(session('error'))
            setTimeout(() => window.toast('{{ session('error') }}', 'error', 'Error'), 500);
        @endif
    " @vortex-toast.window="
        $dispatch('add-toast', $event.detail)
    " @resize.window="if (window.innerWidth > 1024) sidebarOpen = true">

    <!-- Global Toaster -->
    <div x-data="{ 
        toasts: [],
        add(toast) {
            const id = Date.now();
            this.toasts.push({ id, ...toast });
            setTimeout(() => this.remove(id), 5000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }" @add-toast.window="add($event.detail)"
    class="fixed top-6 right-6 z-[200] flex flex-col gap-3 pointer-events-none w-full max-w-[400px]">
        <template x-for="t in toasts" :key="t.id">
            <div x-show="true" 
                x-init="lucide.createIcons()"
                x-transition:enter="transition ease-out duration-300 transform" 
                x-transition:enter-start="opacity-0 translate-x-12" 
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-200 transform" 
                x-transition:leave-start="opacity-100 translate-x-0" 
                x-transition:leave-end="opacity-0 translate-x-12"
                class="pointer-events-auto bg-white/80 dark:bg-dark-alt/80 backdrop-blur-xl border-l-4 p-5 rounded-[24px] shadow-2xl flex items-start gap-4 animate-in slide-in-from-right-8"
                :class="{
                    'border-emerald-500 shadow-emerald-500/10': t.type === 'success',
                    'border-red-500 shadow-red-500/10': t.type === 'error',
                    'border-primary shadow-primary/10': t.type === 'info'
                }">
                <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center"
                    :class="{
                        'bg-emerald-500/10 text-emerald-500': t.type === 'success',
                        'bg-red-500/10 text-red-500': t.type === 'error',
                        'bg-primary/10 text-primary': t.type === 'info'
                    }">
                    <i :data-lucide="t.type === 'success' ? 'check-circle' : (t.type === 'error' ? 'alert-circle' : 'info')" class="w-5 h-5"></i>
                </div>
                <div class="flex-1">
                    <h5 class="text-[10px] font-black uppercase tracking-[0.2em] italic mb-1"
                        :class="t.type === 'success' ? 'text-emerald-600' : (t.type === 'error' ? 'text-red-600' : 'text-primary')"
                        x-text="t.title || 'Alerta del Sistema'"></h5>
                    <p class="text-[13px] font-black italic dark:text-gray-100 leading-tight" x-text="t.message"></p>
                </div>
                <button @click="remove(t.id)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        </template>
    </div>

    @if(Auth::check() && Auth::user()->is_demo)
        <!-- Floating Demo Banner -->
        <div x-data="{
                showBanner: false, // Start hidden to prevent flash if it should be hidden
                checkBanner() {
                    const hideUntil = localStorage.getItem('demoBannerHideUntil');
                    const now = new Date().getTime();
                    
                    if (!hideUntil || now > hideUntil) {
                        this.showBanner = true; // Show it now
                    } else {
                        // Still hidden, calculate remaining time and schedule show
                        const remaining = hideUntil - now;
                        setTimeout(() => {
                            this.showBanner = true;
                        }, remaining);
                    }
                },
                closeBanner() {
                    this.showBanner = false;
                    const hideDuration = 120000; // 120,000 ms = 2 minutes
                    const now = new Date().getTime();
                    const hideUntil = now + hideDuration;
                    
                    localStorage.setItem('demoBannerHideUntil', hideUntil);
                    
                    setTimeout(() => {
                        this.showBanner = true;
                    }, hideDuration);
                }
            }" 
            x-init="checkBanner()"
            x-show="showBanner"
            x-transition:enter="transition ease-out duration-500 transform"
            x-transition:enter-start="opacity-0 translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-300 transform"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-4"
            class="fixed top-20 right-6 z-[100] animate-float" x-cloak>
            
            <div
                class="bg-amber-500/90 backdrop-blur-md shadow-2xl shadow-amber-500/20 border border-amber-400 rounded-2xl p-4 flex flex-col gap-2 w-64 relative">
                
                <!-- Close Button -->
                <button @click="closeBanner()" class="absolute top-2 right-2 text-amber-100 hover:text-white transition-colors bg-amber-600/30 hover:bg-amber-600 p-1 rounded-lg" title="Minimizar">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>

                <div class="flex items-center gap-2 text-white">
                    <i data-lucide="alert-triangle" class="w-5 h-5 animate-pulse"></i>
                    <span class="text-sm font-black uppercase tracking-widest leading-none drop-shadow-md">Entorno Demo</span>
                </div>
                <p class="text-[11px] text-amber-50 font-medium leading-tight drop-shadow-sm pr-2">
                    Esta sesión caduca en <b>1 hora</b>. Registrate para guardar tus cambios y obtener acceso real.
                </p>
                <a href="{{ route('register') }}"
                    class="mt-1 bg-white text-amber-600 hover:bg-amber-50 px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-widest text-center shadow-md transition-all active:scale-95 group flex items-center justify-center gap-1">
                    Crear cuenta real <i data-lucide="arrow-right"
                        class="w-3 h-3 group-hover:translate-x-1 transition-transform"></i>
                </a>
            </div>
        </div>
    @endif

    <div class="flex h-screen overflow-hidden relative">
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
            class="fixed inset-0 bg-dark/60 backdrop-blur-sm z-[60] lg:hidden" x-cloak>
        </div>

        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-[70] lg:relative lg:z-0 transition-transform duration-300 transform"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            @include('partials.sidebar')
        </div>

        <!-- Content Area -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navigation -->
            @include('partials.top-nav')

            <!-- Main Scrollable Content -->
            <div class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8 bg-gray-50/50 dark:bg-dark page-transition">
                @yield('content')
            </div>
        </main>

    </div>
    
    @include('partials.search-modal')

    <!-- Initialize Lucide Icons -->
    <script>
        window.addEventListener('load', () => {
            lucide.createIcons();
        });

        function initSearchableSelects(root = document) {
            if (typeof TomSelect === 'undefined') return;

            const selects = root.querySelectorAll('select:not([data-search-disabled])');
            selects.forEach((select) => {
                if (select.tomselect) return;

                const placeholderOption = select.querySelector('option[value=""]');
                new TomSelect(select, {
                    create: false,
                    allowEmptyOption: true,
                    maxOptions: 2000,
                    placeholder: select.dataset.placeholder || placeholderOption?.textContent?.trim() || 'Buscar...',
                    searchField: ['text'],
                });
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            initSearchableSelects();
        });

        window.addEventListener('vortex:refresh-selects', (event) => {
            initSearchableSelects(event.detail?.root || document);
        });
    </script>
</body>

</html>