<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Fase 11: las ventas dejan de depender de inventory_items (celulares,
     * Vortex) y pasan a apoyarse en products (Fase 5). inventory_item_id se
     * deja intacto (ya era nullable) para no romper el modulo de reparacion
     * de celulares, que queda desactivado pero no borrado.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('inventory_item_id')
                ->constrained()->onDelete('set null');
        });

        Schema::table('sales', function (Blueprint $table) {
            // Vortex facturaba en USD (celulares); Mito Yamile opera en ARS.
            $table->string('currency', 3)->default('ARS')->change();
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->change();
        });
    }
};
