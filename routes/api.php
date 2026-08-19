<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PhysicalCountController;
use App\Http\Controllers\Api\PriceListController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\RepairController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SparePartController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TechnicianController;
use App\Http\Controllers\Api\Arca\ArcaController;
use App\Http\Controllers\Api\Arca\ArcaLogController;
use App\Http\Controllers\Api\Arca\CertificateController;
use App\Http\Controllers\Api\Arca\FiscalSettingController;
use App\Http\Controllers\Api\Arca\InvoiceController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->as('api.')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('clients', ClientController::class);

    // ── Cuenta corriente (Fase 13) ───────────────────────────────────────────
    Route::get('clients/{client}/account-movements', [AccountController::class, 'movements'])->name('clients.account-movements');
    Route::post('clients/{client}/account-payments', [AccountController::class, 'storePayment'])->name('clients.account-payments');

    Route::apiResource('inventory', InventoryController::class);

    // ── Catalogo (Fase 5) ────────────────────────────────────────────────────
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('products', ProductController::class);

    // ── Stock (Fase 6) ───────────────────────────────────────────────────────
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('products/{product}/stock-movements', [StockController::class, 'movements'])->name('stock.movements');
    Route::post('products/{product}/stock-movements', [StockController::class, 'storeMovement'])->name('stock.movements.store');

    // ── Escaner de codigo de barras (Fase 7) ─────────────────────────────────
    Route::get('stock/lookup', [StockController::class, 'lookup'])->name('stock.lookup');

    // ── Inventario fisico (Fase 8) ───────────────────────────────────────────
    Route::prefix('physical-counts')->as('physical-counts.')->group(function () {
        Route::get('/', [PhysicalCountController::class, 'index'])->name('index');
        Route::post('/', [PhysicalCountController::class, 'store'])->name('store');
        Route::get('/{physicalCount}', [PhysicalCountController::class, 'show'])->name('show');
        Route::put('/{physicalCount}/counts', [PhysicalCountController::class, 'saveCounts'])->name('counts');
        Route::post('/{physicalCount}/finalize', [PhysicalCountController::class, 'finalize'])->name('finalize');
        Route::post('/{physicalCount}/cancel', [PhysicalCountController::class, 'cancel'])->name('cancel');
    });

    // ── Listas de precios (Fase 10) ──────────────────────────────────────────
    Route::apiResource('price-lists', PriceListController::class);
    Route::put('price-lists/{priceList}/items', [PriceListController::class, 'syncItems'])->name('price-lists.items');

    // ── Compras a proveedores (Fase 12) ──────────────────────────────────────
    Route::apiResource('purchases', PurchaseController::class)->only(['index', 'store', 'show']);
    Route::post('purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');
    Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');

    // ->only(...): el controller no implementa update() (una venta no se edita, se anula).
    Route::apiResource('sales', SaleController::class)->only(['index', 'store', 'show', 'destroy']);

    // ── Pedidos (Fase 17) ────────────────────────────────────────────────────
    Route::apiResource('orders', \App\Http\Controllers\Api\OrderController::class)->only(['index', 'store', 'show']);
    Route::post('orders/{order}/confirm', [\App\Http\Controllers\Api\OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('orders/{order}/fulfill', [\App\Http\Controllers\Api\OrderController::class, 'fulfill'])->name('orders.fulfill');
    Route::post('orders/{order}/cancel', [\App\Http\Controllers\Api\OrderController::class, 'cancel'])->name('orders.cancel');

    // ── Caja (Fase 14) ───────────────────────────────────────────────────────
    // 'current' antes del apiResource: si no, {cashSession} intentaria resolver "current" como id.
    Route::get('cash-sessions/current', [\App\Http\Controllers\Api\CashSessionController::class, 'current'])->name('cash-sessions.current');
    Route::apiResource('cash-sessions', \App\Http\Controllers\Api\CashSessionController::class)->only(['index', 'store', 'show']);
    Route::post('cash-sessions/{cashSession}/movements', [\App\Http\Controllers\Api\CashSessionController::class, 'storeMovement'])->name('cash-sessions.movements.store');
    Route::post('cash-sessions/{cashSession}/close', [\App\Http\Controllers\Api\CashSessionController::class, 'close'])->name('cash-sessions.close');
    Route::apiResource('repairs', RepairController::class);
    Route::apiResource('technicians', TechnicianController::class);
    Route::apiResource('spare-parts', SparePartController::class);

    // ── ARCA Facturación ─────────────────────────────────────────────────────
    Route::prefix('arca')->as('arca.')->group(function () {
        Route::post('fiscal-settings', [FiscalSettingController::class, 'store'])->name('fiscal-settings.store');
        Route::get('fiscal-settings', [FiscalSettingController::class, 'show'])->name('fiscal-settings.show');

        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::post('invoices/{invoice}/authorize', [InvoiceController::class, 'authorize'])->name('invoices.authorize');
        Route::post('invoices/{invoice}/emitir-afip-sdk', [InvoiceController::class, 'emitirAfipSdk'])->name('invoices.emitir-afip-sdk');
        Route::post('invoices/{invoice}/generate-pdf', [InvoiceController::class, 'generatePdf'])->name('invoices.generate-pdf');
        Route::get('invoices/{invoice}/download-pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.download-pdf');

        Route::post('test-connection', [ArcaController::class, 'testConnection'])->name('test-connection');

        Route::get('certificate', [CertificateController::class, 'show'])->name('certificate.show');
        Route::post('certificate', [CertificateController::class, 'store'])->name('certificate.store');
        Route::post('certificate/validate', [CertificateController::class, 'validateCertificate'])->name('certificate.validate');

        Route::get('logs', [ArcaLogController::class, 'index'])->name('logs.index');
        Route::get('logs/{log}', [ArcaLogController::class, 'show'])->name('logs.show');
    });
});
