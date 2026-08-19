<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('physical_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

            // code se completa despues del insert, derivado del id (mismo patron que
            // products.internal_code). Ej: INV-000001.
            $table->string('code')->nullable();

            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('status')->default('open'); // App\Enums\PhysicalCountStatus
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('organization_id');
            $table->unique(['organization_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_counts');
    }
};
