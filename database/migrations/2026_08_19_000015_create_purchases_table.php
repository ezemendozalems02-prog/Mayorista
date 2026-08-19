<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');

            // code se completa despues del insert, derivado del id (mismo patron que
            // products.internal_code / physical_counts.code). Ej: PO-000001.
            $table->string('code')->nullable();

            $table->string('status')->default('pending'); // App\Enums\PurchaseStatus
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('received_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('status');
            $table->unique(['organization_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
