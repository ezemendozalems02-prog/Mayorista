<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RepairController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\SparePartController;
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
    Route::apiResource('inventory', InventoryController::class);

    // ── Catalogo (Fase 5) ────────────────────────────────────────────────────
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('sales', SaleController::class);
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
