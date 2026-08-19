<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Cache derivado: quantity = suma de stock_movements.quantity_delta para ese producto.
        // Nunca se escribe a mano; solo la actualiza App\Services\StockService al registrar
        // un movimiento (misma transaccion, con lockForUpdate para evitar carreras).
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->unique()->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
    }
};
