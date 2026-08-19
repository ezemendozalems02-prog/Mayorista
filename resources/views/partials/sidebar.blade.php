<aside class="w-64 h-full bg-white dark:bg-dark-alt border-r border-gray-100 dark:border-white/5">
    <div class="flex flex-col h-full bg-white dark:bg-dark-alt relative overflow-hidden">

        <!-- Logo -->
        <div class="py-3 flex items-center justify-center">
            <div class="flex items-center">
                @if(config('platform.mode') === 'single_license')
                    <span class="text-xl font-black italic text-violet-gradient tracking-tighter uppercase px-4 text-center">
                        {{ config('app.name') }}
                    </span>
                @else
                    <img src="{{ asset(config('platform.brand.logo_white')) }}" alt="{{ config('platform.brand.name') }}"
                        class="h-28 w-auto object-contain hover:scale-105 transition-transform duration-300">
                @endif
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-500">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Demo Banner -->
        @if(Auth::check() && Auth::user()->is_demo)
        <div class="mx-4 mb-2 p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl relative overflow-hidden group">
            <div class="absolute inset-0 bg-amber-500/5 rotate-45 scale-150 group-hover:bg-amber-500/10 transition-colors duration-500"></div>
            <div class="relative z-10 flex flex-col">
                <div class="flex items-center gap-2 mb-1 text-amber-600 dark:text-amber-400">
                    <i data-lucide="alert-triangle" class="w-4 h-4 animate-pulse"></i>
                    <span class="text-[11px] font-black uppercase tracking-widest">Modo Demo</span>
                </div>
                <p class="text-[10px] text-amber-700/80 dark:text-amber-300/80 font-medium leading-tight">
                    Los datos son temporales y se eliminarán pronto.
                </p>
                @if(Route::has('register'))
                <a href="{{ route('register') }}" class="mt-2 text-[10px] font-bold text-amber-600 hover:text-amber-700 underline underline-offset-2">Crear cuenta real &rarr;</a>
                @endif
            </div>
        </div>
        @endif

        <!-- User Profile Bar -->
        <div class="px-6 pb-6 mb-2 border-b border-gray-100 dark:border-white/5 bg-transparent">
            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=c084fc2b&color=fff"
                    class="w-10 h-10 rounded-xl shadow-lg border-2 border-white dark:border-dark-alt" alt="User">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-black truncate text-gray-900 dark:text-gray-100 italic">
                        {{ Auth::user()->name }}
                    </p>
                    <p class="text-[10px] text-primary uppercase font-bold tracking-widest">{{ Auth::user()->role }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto scrollbar-hide">

            @php
                $isSuperAdmin = Auth::user()->role === \App\Enums\UserRole::SUPER_ADMIN;
                $userRole = Auth::user()->role instanceof \UnitEnum ? Auth::user()->role->value : Auth::user()->role;

                // Menú para usuarios de negocios
                $businessMenu = [
                    ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'route' => 'dashboard', 'feature' => null, 'roles' => ['owner', 'manager', 'technician', 'seller']],
                    ['label' => 'Inventario', 'icon' => 'package', 'route' => 'inventory.index', 'feature' => 'inventory_basic', 'roles' => ['owner', 'manager', 'technician', 'seller']],
                    ['label' => 'Productos', 'icon' => 'box', 'route' => 'product.index', 'feature' => 'catalog', 'roles' => ['owner', 'manager', 'seller']],
                    ['label' => 'Stock', 'icon' => 'boxes', 'route' => 'stock.index', 'feature' => 'catalog', 'roles' => ['owner', 'manager', 'seller']],
                    ['label' => 'Inventario Físico', 'icon' => 'clipboard-list', 'route' => 'physical-count.index', 'feature' => 'catalog', 'roles' => ['owner', 'manager']],
                    ['label' => 'Categorías', 'icon' => 'folder-tree', 'route' => 'category.index', 'feature' => 'catalog', 'roles' => ['owner', 'manager']],
                    ['label' => 'Marcas', 'icon' => 'tag', 'route' => 'brand.index', 'feature' => 'catalog', 'roles' => ['owner', 'manager']],
                    ['label' => 'Proveedores', 'icon' => 'truck', 'route' => 'supplier.index', 'feature' => 'catalog', 'roles' => ['owner', 'manager']],
                    ['label' => 'Reparaciones', 'icon' => 'wrench', 'route' => 'repair.index', 'feature' => 'repairs', 'roles' => ['owner', 'manager', 'technician']],
                    ['label' => 'Ventas', 'icon' => 'shopping-cart', 'route' => 'sale.index', 'feature' => 'sales', 'roles' => ['owner', 'manager', 'seller']],
                    ['label' => 'Clientes', 'icon' => 'users', 'route' => 'client.index', 'feature' => 'clients', 'roles' => ['owner', 'manager', 'seller', 'technician']],
                    ['label' => 'Canjes', 'icon' => 'refresh-cw', 'route' => 'trade-in.index', 'feature' => 'trade_ins', 'roles' => ['owner', 'manager', 'seller']],
                    ['label' => 'Staff', 'icon' => 'user-check', 'route' => 'technician.index', 'feature' => 'technicians', 'roles' => ['owner', 'manager']],
                    ['label' => 'Sucursales', 'icon' => 'map-pin', 'route' => 'branch.index', 'feature' => 'multi_branch', 'roles' => ['owner', 'manager']],
                    ['label' => 'Reportes', 'icon' => 'bar-chart-3', 'route' => 'report.index', 'feature' => 'reports_basic', 'roles' => ['owner', 'manager']],
                ];

                $org = Auth::user()->organization;
            @endphp

            @if(!$isSuperAdmin)
                {{-- Menú de Negocio (Solo para Owners/Empleados) --}}
                @foreach($businessMenu as $item)
                    @php
                        // Check if user has required role
                        if (!in_array($userRole, $item['roles'])) continue;

                        $hasAccess = $org && (!$item['feature'] || $org->hasFeature($item['feature']));
                    @endphp
                    <a href="{{ $hasAccess ? (Route::has($item['route']) ? route($item['route']) : '#') : route('subscription.index') }}"
                        class="flex items-center px-4 py-3 text-sm font-medium transition-all duration-200 rounded-xl group relative {{ request()->routeIs($item['route']) ? 'bg-primary/10 text-primary active-link' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-gray-100' }} {{ !$hasAccess ? 'opacity-60 cursor-not-allowed' : '' }}">
                        <i data-lucide="{{ $item['icon'] }}"
                            class="w-5 h-5 mr-3 {{ request()->routeIs($item['route']) ? 'text-primary' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"></i>
                        <span>{{ $item['label'] }}</span>
                        @if(!$hasAccess)
                            <i data-lucide="lock" class="w-3.5 h-3.5 ml-auto text-gray-400"></i>
                        @endif
                        @if(request()->routeIs($item['route']))
                            <div class="absolute right-0 w-1 h-6 bg-primary rounded-l-full"></div>
                        @endif
                    </a>
                @endforeach
            @else
                {{-- Menú de Super Admin --}}
                <div class="px-4 py-2 uppercase text-[10px] font-black tracking-widest text-red-500 dark:text-red-400 mb-2">
                    Panel Master
                </div>

                <a href="{{ route('super-admin.dashboard') }}"
                    class="flex items-center px-4 py-3 text-sm font-black transition-all rounded-xl {{ request()->routeIs('super-admin.dashboard') ? 'bg-red-500/10 text-red-500' : 'text-gray-500 dark:text-gray-400 hover:bg-red-500/5 hover:text-red-500' }}">
                    <i data-lucide="shield-check" class="w-5 h-5 mr-3"></i>
                    <span>Dashboard Global</span>
                </a>

                <a href="{{ route('super-admin.organizations.index') }}"
                    class="flex items-center px-4 py-3 text-sm font-black transition-all rounded-xl {{ request()->routeIs('super-admin.organizations.*') ? 'bg-red-500/10 text-red-500' : 'text-gray-500 dark:text-gray-400 hover:bg-red-500/5 hover:text-red-500' }}">
                    <i data-lucide="building-2" class="w-5 h-5 mr-3"></i>
                    <span>Gestionar Negocios</span>
                </a>

                @if(config('platform.mode') === 'saas')
                <a href="{{ route('super-admin.plans.index') }}"
                    class="flex items-center px-4 py-3 text-sm font-black transition-all rounded-xl {{ request()->routeIs('super-admin.plans.*') ? 'bg-red-500/10 text-red-500' : 'text-gray-500 dark:text-gray-400 hover:bg-red-500/5 hover:text-red-500' }}">
                    <i data-lucide="package" class="w-5 h-5 mr-3"></i>
                    <span>Gestionar Planes</span>
                </a>
                @endif
            @endif

            {{-- ── Facturación ARCA ── --}}
            @php
                $hasArca = $org && $org->hasFeature('arca_billing');
                $arcaRoles = ['owner', 'manager'];
            @endphp
            @if(!$isSuperAdmin && in_array($userRole, $arcaRoles))
                <div class="pt-6 pb-2 px-4 uppercase text-[10px] font-bold tracking-widest text-gray-400 dark:text-gray-500">
                    Facturación ARCA
                </div>
                @foreach([
                    ['label' => 'Configuración', 'icon' => 'sliders-horizontal', 'route' => 'arca.configuracion'],
                    ['label' => 'Nueva Factura',  'icon' => 'file-plus',          'route' => 'arca.nueva'],
                    ['label' => 'Comprobantes',   'icon' => 'file-text',          'route' => 'arca.comprobantes'],
                    ['label' => 'Logs ARCA',      'icon' => 'activity',           'route' => 'arca.logs'],
                ] as $arcaItem)
                    <a href="{{ $hasArca ? route($arcaItem['route']) : route('subscription.index') }}"
                        class="flex items-center px-4 py-3 text-sm font-medium transition-all duration-200 rounded-xl group relative {{ request()->routeIs($arcaItem['route']) ? 'bg-primary/10 text-primary active-link' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-gray-100' }} {{ !$hasArca ? 'opacity-60 cursor-not-allowed' : '' }}">
                        <i data-lucide="{{ $arcaItem['icon'] }}"
                            class="w-5 h-5 mr-3 {{ request()->routeIs($arcaItem['route']) ? 'text-primary' : 'text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"></i>
                        <span>{{ $arcaItem['label'] }}</span>
                        @if(!$hasArca)
                            <i data-lucide="lock" class="w-3.5 h-3.5 ml-auto text-gray-400"></i>
                        @endif
                        @if(request()->routeIs($arcaItem['route']))
                            <div class="absolute right-0 w-1 h-6 bg-primary rounded-l-full"></div>
                        @endif
                    </a>
                @endforeach
            @endif

            <!-- Divider -->
            <div
                class="pt-6 pb-2 px-4 uppercase text-[10px] font-bold tracking-widest text-gray-400 dark:text-gray-500">
                Sistema</div>

            @if(!$isSuperAdmin && in_array($userRole, ['owner', 'manager']) && config('platform.mode') !== 'single_license')
                <a href="{{ route('payment.index') }}"
                    class="flex items-center px-4 py-3 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:text-gray-900 rounded-xl transition-all {{ request()->routeIs('settings') ? 'bg-primary/10 text-primary' : '' }}">
                    <i data-lucide="credit-card" class="w-5 h-5 mr-3"></i>
                    <span>Suscripción (Plan)</span>
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}" id="logout-form">
                @csrf
                <button type="submit"
                    class="flex items-center w-full px-4 py-3 mt-4 text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-xl transition-all group">
                    <i data-lucide="log-out" class="w-5 h-5 mr-3 group-hover:translate-x-1 transition-transform"></i>
                    <span>Cerrar Sesión</span>
                </button>
            </form>

            <!-- Dolar Status Widget -->
            <div x-show="dolarRate" x-cloak
                class="mt-8 p-4 rounded-2xl bg-gray-50 dark:bg-dark border border-gray-100 dark:border-white/5 animate-in fade-in duration-500">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-1">
                        <i data-lucide="trending-up" class="w-3 h-3 text-emerald-500"></i> Dólar Blue
                    </p>
                    <span class="relative flex h-2 w-2">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                </div>
                <p class="text-lg font-black text-gray-900 dark:text-gray-100 italic tracking-tight">
                    <span class="text-emerald-500">$</span><span x-text="dolarRate"></span> <span
                        class="text-xs font-bold text-gray-400 uppercase tracking-widest">ARS</span>
                </p>
            </div>
        </nav>


    </div>
</aside>