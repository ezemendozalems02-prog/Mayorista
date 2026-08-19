<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('physical_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('physical_count_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');

            // Snapshot del stock del sistema al momento de arrancar el conteo.
            $table->integer('expected_quantity')->default(0);
            // Null = todavia no se conto este producto.
            $table->integer('counted_quantity')->nullable();
            $table->timestamp('counted_at')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['physical_count_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_count_items');
    }
};
