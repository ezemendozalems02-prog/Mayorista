<?php

use App\Http\Controllers\Web\ArcaController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BranchController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\RepairController;
use App\Http\Controllers\Web\SaleController;
use App\Http\Controllers\Web\ClientController;
use App\Http\Controllers\Web\TechnicianController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Vortex Control Phone SaaS (Blade Version)
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
