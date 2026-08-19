<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Ledger append-only: nunca se actualiza ni se borra una fila de aca.
        // Un error se corrige con un movimiento compensatorio nuevo, no editando el viejo.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            $table->string('type'); // App\Enums\StockMovementType
            $table->integer('quantity_delta'); // signo = direccion: + entrada, - salida (nunca 0)
            $table->decimal('unit_cost', 12, 2)->nullable(); // costo al momento del movimiento

            // Referencia opcional al documento de origen (compra, venta, conteo...),
            // sin FK real todavia porque esas tablas no existen hasta fases futuras.
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamp('created_at')->useCurrent();

            $table->index('organization_id');
            $table->index(['product_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
