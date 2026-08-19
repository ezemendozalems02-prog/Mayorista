<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('name');                        // Ej: "Sucursal Centro", "Local Palermo"
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();    // Nombre del encargado
            $table->boolean('is_active')->default(true);
            $table->boolean('is_main')->default(false);   // Sucursal principal
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
        });

        // Add branch_id to inventory_items
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('organization_id')->constrained()->onDelete('set null');
            $table->index('branch_id');
        });

        // Add branch_id to sales
        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('organization_id')->constrained()->onDelete('set null');
        });

        // Add branch_id to repairs
        Schema::table('repairs', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('organization_id')->constrained()->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('repairs', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::dropIfExists('branches');
    }
};
