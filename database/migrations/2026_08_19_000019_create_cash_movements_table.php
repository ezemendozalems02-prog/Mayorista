<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ledger append-only de movimientos de caja (Fase 14): mismo principio
     * que stock_movements (Fase 6) y account_movements (Fase 13). Nunca se
     * actualiza ni se borra una fila.
     */
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('cash_session_id')->constrained()->onDelete('cascade');

            $table->string('type'); // App\Enums\CashMovementType
            $table->decimal('amount', 12, 2); // signo = direccion: + entra, - sale (nunca 0)

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamp('created_at')->useCurrent();

            $table->index('organization_id');
            $table->index(['cash_session_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};
