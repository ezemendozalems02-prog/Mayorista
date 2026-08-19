<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Caja (Fase 14): cabecera de un turno de caja. A diferencia de los
     * ledgers append-only (stock_movements, account_movements), esta si es
     * un registro mutable -- se actualiza una vez, al cerrar el turno.
     */
    public function up(): void
    {
        Schema::create('cash_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

            $table->string('status')->default('open'); // App\Enums\CashSessionStatus

            $table->decimal('opening_amount', 12, 2);
            $table->decimal('closing_amount', 12, 2)->nullable();  // contado fisicamente al cerrar (arqueo)
            $table->decimal('expected_amount', 12, 2)->nullable(); // SUM(cash_movements) al momento del cierre
            $table->decimal('difference', 12, 2)->nullable();      // closing_amount - expected_amount

            $table->foreignId('opened_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');

            $table->text('notes')->nullable();

            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_sessions');
    }
};
