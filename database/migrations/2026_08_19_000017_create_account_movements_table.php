<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ledger append-only de cuenta corriente (Fase 13): igual principio que
     * stock_movements (Fase 6). Nunca se actualiza ni se borra una fila; un
     * error se corrige con un movimiento compensatorio nuevo.
     */
    public function up(): void
    {
        Schema::create('account_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');

            $table->string('type'); // App\Enums\AccountMovementType
            $table->decimal('amount', 12, 2); // signo = direccion: + deuda, - pago/credito (nunca 0)

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamp('created_at')->useCurrent();

            $table->index('organization_id');
            $table->index(['client_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_movements');
    }
};
