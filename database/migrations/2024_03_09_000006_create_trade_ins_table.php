<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trade_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('inventory_item_id')->nullable()->constrained()->onDelete('set null'); // The item created from this trade-in
            $table->string('brand')->default('Apple');
            $table->string('model');
            $table->string('imei')->nullable();
            $table->text('condition')->nullable();
            $table->decimal('appraised_value', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_ins');
    }
};
