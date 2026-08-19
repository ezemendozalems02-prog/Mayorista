<?php

use App\Http\Controllers\Web\ArcaController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BrandController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\PhysicalCountController;
use App\Http\Controllers\Web\PriceListController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\PurchaseController;
use App\Http\Controllers\Web\RepairController;
use App\Http\Controllers\Web\SaleController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\StockController;
use App\Http\Controllers\Web\SupplierController;
use App\Http\Controllers\Web\TechnicianController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Mito Yamile (Blade Version)
|--------------------------------------------------------------------------
*/

// Redirección inicial
Route::get('/', function () {
    return redirect()->route('login');
});

// Demo Mode
Route::get('/demo', [\App\Http\Controllers\DemoController::class, 'start'])->name('demo.start');


// Autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset
Route::get('/forgot-password', [\App\Http\Controllers\Web\PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [\App\Http\Controllers\Web\PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [\App\Http\Controllers\Web\PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [\App\Http\Controllers\Web\PasswordResetController::class, 'reset'])->name('password.update');

// Webhook de Mercado Pago - Público (Debe estar fuera de 'auth')
Route::post('/suscripcion/webhook', [\App\Http\Controllers\Web\SubscriptionController::class, 'webhook'])->name('subscription.webhook');

// ── Catálogo público (Fase 19) — sin login, para compartir con clientes ────
Route::get('/catalogo', [\App\Http\Controllers\Web\CatalogController::class, 'index'])->name('catalog.public');


// Dashboard & Privado
Route::middleware(['auth', \App\Http\Middleware\DemoMiddleware::class])->group(function () {

    // ─── Global Search ───────────────────────────────────────────────
    Route::get('/global-search', [\App\Http\Controllers\Web\SearchController::class, 'search'])->name('global.search');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulos
    Route::middleware('role:owner,manager,technician,seller')->prefix('inventario')->group(function () {
        Route::get('/importar', [\App\Http\Controllers\Web\StockImportController::class, 'index'])->name('inventory.import.index')->middleware('role:owner,manager');
        Route::get('/importar/plantilla', [\App\Http\Controllers\Web\StockImportController::class, 'template'])->name('inventory.import.template')->middleware('role:owner,manager');
        Route::post('/importar/upload', [\App\Http\Controllers\Web\StockImportController::class, 'upload'])->name('inventory.import.upload')->middleware('role:owner,manager');
        Route::post('/importar/preview', [\App\Http\Controllers\Web\StockImportController::class, 'preview'])->name('inventory.import.preview')->middleware('role:owner,manager');
        Route::post('/importar/store', [\App\Http\Controllers\Web\StockImportController::class, 'store'])->name('inventory.import.store')->middleware('role:owner,manager');
    });

    Route::resource('inventario', InventoryController::class)->parameters(['inventario' => 'inventory'])->names([
        'index' => 'inventory.index',
        'create' => 'inventory.create',
        'store' => 'inventory.store',
        'show' => 'inventory.show',
        'edit' => 'inventory.edit',
        'update' => 'inventory.update',
        'destroy' => 'inventory.destroy',
    ])->middleware('role:owner,manager,technician,seller');

    Route::resource('reparaciones', RepairController::class)->parameters(['reparaciones' => 'repair'])->names([
        'index' => 'repair.index',
        'create' => 'repair.create',
        'store' => 'repair.store',
        'show' => 'repair.show',
        'edit' => 'repair.edit',
        'update' => 'repair.update',
        'destroy' => 'repair.destroy',
    ])->middleware(['feature:repairs', 'role:owner,manager,technician']);

    Route::resource('ventas', SaleController::class)->parameters(['ventas' => 'sale'])->names([
        'index' => 'sale.index',
        'create' => 'sale.create',
        'store' => 'sale.store',
        'show' => 'sale.show',
        'destroy' => 'sale.destroy',
    ])->middleware(['feature:sales', 'role:owner,manager,seller']);

    Route::get('ventas/{sale}/ticket', [SaleController::class, 'downloadTicket'])
        ->name('sale.ticket')
        ->middleware(['feature:sales', 'role:owner,manager,seller']);

    // Clientes
    Route::resource('clientes', ClientController::class)->parameters(['clientes' => 'client'])->names([
        'index' => 'client.index',
        'create' => 'client.create',
        'store' => 'client.store',
        'show' => 'client.show',
        'edit' => 'client.edit',
        'update' => 'client.update',
        'destroy' => 'client.destroy',
    ])->middleware(['feature:clients', 'role:owner,manager,seller,technician']);
    Route::post('/clientes/quick-store', [ClientController::class, 'quickStore'])->name('client.quick-store')->middleware('feature:clients');

    // ── Cuenta corriente (Fase 13) ───────────────────────────────────────────
    Route::get('/clientes/{client}/cuenta-corriente', [\App\Http\Controllers\Web\AccountController::class, 'show'])->name('client.account')->middleware('feature:clients');
    Route::post('/clientes/{client}/cuenta-corriente/pagos', [\App\Http\Controllers\Web\AccountController::class, 'storePayment'])->name('client.account.pay')->middleware('feature:clients');

    // ── Catálogo (Fase 5) ────────────────────────────────────────────────────
    Route::resource('productos', ProductController::class)->except('show')->parameters(['productos' => 'product'])->names([
        'index' => 'product.index',
        'create' => 'product.create',
        'store' => 'product.store',
        'edit' => 'product.edit',
        'update' => 'product.update',
        'destroy' => 'product.destroy',
    ])->middleware(['feature:catalog', 'role:owner,manager,seller']);

    Route::resource('categorias', CategoryController::class)->except('show')->parameters(['categorias' => 'category'])->names([
        'index' => 'category.index',
        'create' => 'category.create',
        'store' => 'category.store',
        'edit' => 'category.edit',
        'update' => 'category.update',
        'destroy' => 'category.destroy',
    ])->middleware(['feature:catalog', 'role:owner,manager']);

    Route::resource('marcas', BrandController::class)->except('show')->parameters(['marcas' => 'brand'])->names([
        'index' => 'brand.index',
        'create' => 'brand.create',
        'store' => 'brand.store',
        'edit' => 'brand.edit',
        'update' => 'brand.update',
        'destroy' => 'brand.destroy',
    ])->middleware(['feature:catalog', 'role:owner,manager']);

    Route::resource('proveedores', SupplierController::class)->except('show')->parameters(['proveedores' => 'supplier'])->names([
        'index' => 'supplier.index',
        'create' => 'supplier.create',
        'store' => 'supplier.store',
        'edit' => 'supplier.edit',
        'update' => 'supplier.update',
        'destroy' => 'supplier.destroy',
    ])->middleware(['feature:catalog', 'role:owner,manager']);

    // ── Stock (Fase 6) ───────────────────────────────────────────────────────
    Route::get('/stock', [StockController::class, 'index'])->name('stock.index')->middleware(['feature:catalog', 'role:owner,manager,seller']);
    Route::get('/stock/buscar', [StockController::class, 'findByBarcode'])->name('stock.find-by-barcode')->middleware(['feature:catalog', 'role:owner,manager,seller']);
    Route::get('/stock/{product}/movimientos', [StockController::class, 'movements'])->name('stock.movements')->middleware(['feature:catalog', 'role:owner,manager,seller']);
    Route::post('/stock/{product}/ajustar', [StockController::class, 'adjust'])->name('stock.adjust')->middleware(['feature:catalog', 'role:owner,manager']);

    // ── Inventario físico (Fase 8) ───────────────────────────────────────────
    Route::prefix('inventario-fisico')->group(function () {
        Route::get('/', [PhysicalCountController::class, 'index'])->name('physical-count.index');
        Route::get('/crear', [PhysicalCountController::class, 'create'])->name('physical-count.create');
        Route::post('/', [PhysicalCountController::class, 'store'])->name('physical-count.store');
        Route::get('/{physicalCount}', [PhysicalCountController::class, 'show'])->name('physical-count.show');
        Route::post('/{physicalCount}/guardar', [PhysicalCountController::class, 'save'])->name('physical-count.save');
        Route::post('/{physicalCount}/finalizar', [PhysicalCountController::class, 'finalize'])->name('physical-count.finalize');
        Route::post('/{physicalCount}/cancelar', [PhysicalCountController::class, 'cancel'])->name('physical-count.cancel');
    })->middleware(['feature:catalog', 'role:owner,manager']);

    // ── Listas de precios (Fase 10) ──────────────────────────────────────────
    Route::prefix('listas-de-precios')->group(function () {
        Route::get('/', [PriceListController::class, 'index'])->name('price-list.index');
        Route::get('/crear', [PriceListController::class, 'create'])->name('price-list.create');
        Route::post('/', [PriceListController::class, 'store'])->name('price-list.store');
        Route::get('/{priceList}', [PriceListController::class, 'show'])->name('price-list.show');
        Route::get('/{priceList}/editar', [PriceListController::class, 'edit'])->name('price-list.edit');
        Route::put('/{priceList}', [PriceListController::class, 'update'])->name('price-list.update');
        Route::delete('/{priceList}', [PriceListController::class, 'destroy'])->name('price-list.destroy');
        Route::post('/{priceList}/items', [PriceListController::class, 'setItem'])->name('price-list.items.set');
    })->middleware(['feature:catalog', 'role:owner,manager']);

    // ── Compras a proveedores (Fase 12) ──────────────────────────────────────
    Route::prefix('compras')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('purchase.index');
        Route::get('/crear', [PurchaseController::class, 'create'])->name('purchase.create');
        Route::post('/', [PurchaseController::class, 'store'])->name('purchase.store');
        Route::get('/{purchase}', [PurchaseController::class, 'show'])->name('purchase.show');
        Route::post('/{purchase}/recibir', [PurchaseController::class, 'receive'])->name('purchase.receive');
        Route::post('/{purchase}/cancelar', [PurchaseController::class, 'cancel'])->name('purchase.cancel');
    })->middleware(['feature:catalog', 'role:owner,manager']);

    // ── Pedidos (Fase 17) ────────────────────────────────────────────────────
    Route::prefix('pedidos')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\OrderController::class, 'index'])->name('order.index');
        Route::get('/crear', [\App\Http\Controllers\Web\OrderController::class, 'create'])->name('order.create');
        Route::post('/', [\App\Http\Controllers\Web\OrderController::class, 'store'])->name('order.store');
        Route::get('/{order}', [\App\Http\Controllers\Web\OrderController::class, 'show'])->name('order.show');
        Route::post('/{order}/confirmar', [\App\Http\Controllers\Web\OrderController::class, 'confirm'])->name('order.confirm');
        Route::post('/{order}/facturar', [\App\Http\Controllers\Web\OrderController::class, 'fulfill'])->name('order.fulfill');
        Route::post('/{order}/cancelar', [\App\Http\Controllers\Web\OrderController::class, 'cancel'])->name('order.cancel');
    })->middleware(['feature:sales', 'role:owner,manager,seller']);

    // ── Caja (Fase 14) ────────────────────────────────────────────────────────
    Route::prefix('caja')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\CashSessionController::class, 'index'])->name('cash-session.index');
        Route::post('/abrir', [\App\Http\Controllers\Web\CashSessionController::class, 'store'])->name('cash-session.store');
        Route::post('/movimientos', [\App\Http\Controllers\Web\CashSessionController::class, 'storeMovement'])->name('cash-session.movements.store');
        Route::post('/cerrar', [\App\Http\Controllers\Web\CashSessionController::class, 'close'])->name('cash-session.close');
        Route::get('/historial', [\App\Http\Controllers\Web\CashSessionController::class, 'history'])->name('cash-session.history');
        Route::get('/{cashSession}', [\App\Http\Controllers\Web\CashSessionController::class, 'show'])->name('cash-session.show');
    })->middleware(['feature:sales', 'role:owner,manager,seller']);

    Route::get('/suscripcion', [\App\Http\Controllers\Web\SubscriptionController::class, 'index'])->name('subscription.index')->middleware('role:owner,manager');
    Route::get('/suscripcion/checkout', [\App\Http\Controllers\Web\SubscriptionController::class, 'checkout'])->name('subscription.checkout')->middleware('role:owner,manager');
    Route::post('/suscripcion/process', [\App\Http\Controllers\Web\SubscriptionController::class, 'process'])->name('subscription.process')->middleware('role:owner,manager');
    Route::get('/suscripcion/success', [\App\Http\Controllers\Web\SubscriptionController::class, 'success'])->name('subscription.success')->middleware('role:owner,manager');


    Route::get('/pagos', function () {
        return redirect()->route('subscription.index');
    })->name('payment.index');

    Route::get('/canjes', [\App\Http\Controllers\Web\TradeInController::class, 'index'])->name('trade-in.index')->middleware(['feature:trade_ins', 'role:owner,manager,seller']);
    Route::resource('tecnicos', TechnicianController::class)->parameters(['tecnicos' => 'technician'])->names([
        'index' => 'technician.index',
        'create' => 'technician.create',
        'store' => 'technician.store',
        'show' => 'technician.show',
        'edit' => 'technician.edit',
        'update' => 'technician.update',
        'destroy' => 'technician.destroy',
    ])->middleware(['feature:technicians', 'role:owner,manager']);

    Route::get('/reportes', [\App\Http\Controllers\Web\ReportController::class, 'index'])->name('report.index')->middleware(['feature:reports_basic', 'role:owner,manager']);
    Route::get('/reportes/realtime', [\App\Http\Controllers\Web\ReportController::class, 'realtime'])->name('report.realtime')->middleware(['feature:reports_basic', 'role:owner,manager']);
    
    Route::get('/negocio', [\App\Http\Controllers\Web\OrganizationController::class, 'index'])->name('organization.settings')->middleware('role:owner,manager');
    Route::patch('/negocio', [\App\Http\Controllers\Web\OrganizationController::class, 'update'])->name('organization.update')->middleware('role:owner,manager');

    // Sucursales (Multi-branch) - Plan Pro
    Route::resource('sucursales', BranchController::class)->parameters(['sucursales' => 'branch'])->names([
        'index' => 'branch.index',
        'create' => 'branch.create',
        'store' => 'branch.store',
        'show' => 'branch.show',
        'edit' => 'branch.edit',
        'update' => 'branch.update',
        'destroy' => 'branch.destroy',
    ])->middleware(['feature:multi_branch', 'role:owner,manager']);
    Route::post('/sucursales/{branch}/set-main', [BranchController::class, 'setMain'])->name('branch.set-main')->middleware(['feature:multi_branch', 'role:owner,manager']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\Web\NotificationController::class, 'index'])->name('notification.index');
    Route::get('/notifications/check', function () {
        return response()->json([
            'unread' => auth()->user()->unreadNotifications->take(5),
            'count' => auth()->user()->unreadNotifications->count()
        ]);
    })->name('notifications.check');

    Route::post('/notifications/{id}/mark-as-read', [\App\Http\Controllers\Web\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Web\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\Web\NotificationController::class, 'destroy'])->name('notifications.destroy');

    // ─── Facturación ARCA ────────────────────────────────────────────
    Route::prefix('facturacion')->name('arca.')->middleware(['feature:arca_billing', 'role:owner,manager'])->group(function () {
        Route::get('/',                                    [ArcaController::class, 'configuracion'])->name('configuracion');
        Route::post('/configuracion',                      [ArcaController::class, 'guardarConfiguracion'])->name('guardar-configuracion');
        Route::post('/certificado',                        [ArcaController::class, 'guardarCertificado'])->name('guardar-certificado');
        Route::post('/certificado/validar',                [ArcaController::class, 'validarCertificado'])->name('validar-certificado');
        Route::post('/test-connection',                    [ArcaController::class, 'testConnection'])->name('test-connection');
        Route::get('/nueva',                               [ArcaController::class, 'nueva'])->name('nueva');
        Route::post('/nueva',                              [ArcaController::class, 'storeFactura'])->name('store-factura');
        Route::get('/comprobantes',                        [ArcaController::class, 'comprobantes'])->name('comprobantes');
        Route::get('/comprobantes/{invoice}',              [ArcaController::class, 'showFactura'])->name('show-factura');
        Route::post('/comprobantes/{invoice}/autorizar',   [ArcaController::class, 'autorizarFactura'])->name('autorizar');
        Route::post('/comprobantes/{invoice}/pdf',         [ArcaController::class, 'generarPdf'])->name('generar-pdf');
        Route::get('/comprobantes/{invoice}/descargar-pdf',[ArcaController::class, 'descargarPdf'])->name('descargar-pdf');
        Route::get('/logs',                                [ArcaController::class, 'logs'])->name('logs');
        Route::get('/logs/{log}',                          [ArcaController::class, 'showLog'])->name('show-log');
        Route::post('/generar-csr',                        [ArcaController::class, 'generateCsr'])->name('generar-csr');
    });

    // ─── Super Admin Panel ───────────────────────────────────────────
    Route::middleware(['super_admin'])->prefix('admin')->name('super-admin.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\SuperAdmin\SuperAdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('negocios', \App\Http\Controllers\Web\SuperAdmin\OrganizationController::class)
            ->only(['index', 'show'])
            ->names('organizations')
            ->parameters(['negocios' => 'organization']);

        Route::post('negocios/{organization}/toggle-status', [\App\Http\Controllers\Web\SuperAdmin\OrganizationController::class, 'toggleStatus'])
            ->name('organizations.toggle-status');

        Route::patch('negocios/{organization}/update-plan', [\App\Http\Controllers\Web\SuperAdmin\OrganizationController::class, 'updatePlan'])
            ->name('organizations.update-plan');

        Route::resource('planes', \App\Http\Controllers\Web\SuperAdmin\SubscriptionPlanController::class)
            ->names('plans')
            ->parameters(['planes' => 'subscriptionPlan']);
    });
});
