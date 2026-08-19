<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('category'); // iphone, ipad, macbook, etc.
            $table->string('brand')->default('Apple');
            $table->string('model');
            $table->string('storage')->nullable();
            $table->string('color')->nullable();
            $table->string('imei')->nullable();
            $table->string('serial_number')->nullable();
            $table->integer('battery_health')->nullable();
            $table->string('cosmetic_condition')->nullable(); // Como nueva, con detalles, etc.
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('sale_price', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('in_stock'); // App\Enums\InventoryStatus
            $table->string('stock_type')->default('available'); // available, technical
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null'); // Case of trade-in
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('status');
            $table->index('imei');
            $table->index('serial_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
