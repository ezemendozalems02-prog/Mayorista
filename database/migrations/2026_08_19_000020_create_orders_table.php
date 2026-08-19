<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Pedidos (Fase 17): un compromiso de venta que NO toca stock ni cobra
     * hasta que se cumple (fulfill) -- ahi se convierte en una Sale real via
     * SaleService (Fase 11), que es quien de verdad descuenta stock y cobra.
     * Util para el flujo mayorista tipico: el cliente encarga hoy, se prepara
     * y se factura despues.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('restrict');

            $table->string('code')->nullable(); // ORD-000001, autogenerado desde el id (ver Order::booted())
            $table->string('status')->default('draft'); // App\Enums\OrderStatus

            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            // Se completa recien al cumplir el pedido (fulfill). onDelete('set null'):
            // si la venta se anula despues, el pedido no debe quedar con una FK rota.
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('fulfilled_at')->nullable();

            $table->timestamps();

            $table->index('organization_id');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
