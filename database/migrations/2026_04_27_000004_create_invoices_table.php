<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->string('cliente_nombre');
            $table->string('cliente_documento')->nullable();
            $table->string('cliente_condicion_iva')->nullable();
            $table->enum('tipo_comprobante', ['A', 'B', 'C']);
            $table->unsignedInteger('punto_venta');
            $table->unsignedInteger('numero_comprobante')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('iva', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('cae')->nullable();
            $table->date('cae_vencimiento')->nullable();
            $table->enum('estado', ['PENDIENTE', 'AUTORIZADO', 'RECHAZADO', 'ERROR'])->default('PENDIENTE');
            $table->text('error_message')->nullable();
            $table->json('arca_response')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index(['organization_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
