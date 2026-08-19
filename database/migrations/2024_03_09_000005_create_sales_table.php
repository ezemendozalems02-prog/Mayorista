<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('seller_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('sale_number')->unique();
            $table->string('status')->default('completed'); // draft, completed, cancelled
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 15, 2)->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('cost_total', 15, 2)->default(0);
            $table->decimal('profit_total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('status');
            $table->index('sold_at');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_item_id')->nullable()->constrained()->onDelete('set null');
            $table->string('item_name');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->decimal('line_total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
