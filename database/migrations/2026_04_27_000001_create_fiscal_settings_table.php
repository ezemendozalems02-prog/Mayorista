<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fiscal_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->unique()->constrained()->onDelete('cascade');
            $table->string('cuit', 11);
            $table->string('razon_social');
            $table->enum('condicion_iva', ['MONOTRIBUTO', 'RESPONSABLE_INSCRIPTO', 'EXENTO']);
            $table->unsignedInteger('punto_venta');
            $table->enum('tipo_comprobante_default', ['A', 'B', 'C'])->default('B');
            $table->enum('ambiente', ['TESTING', 'PRODUCTION'])->default('TESTING');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_settings');
    }
};
