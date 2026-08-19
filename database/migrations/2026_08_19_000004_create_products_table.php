<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null');

            // internal_code se completa despues del insert, derivado del id (ver App\Models\Product::booted).
            $table->string('internal_code')->nullable();
            $table->string('barcode')->nullable();

            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('cost', 12, 2)->default(0);
            $table->decimal('retail_price', 12, 2)->nullable();
            $table->decimal('wholesale_price', 12, 2)->nullable();

            $table->integer('min_stock')->default(0);
            $table->string('image_path')->nullable();

            $table->string('status')->default('active'); // App\Enums\ProductStatus

            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('status');
            $table->unique(['organization_id', 'internal_code']);
            $table->unique(['organization_id', 'barcode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
