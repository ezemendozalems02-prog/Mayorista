<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Cliente mayorista (revendedor) vs. minorista (consumidor final).
            // Determina que precio ve por defecto en Ventas (Fase 11).
            $table->string('client_type')->default('retail')->after('full_name'); // App\Enums\ClientType

            // Razon social, para clientes mayoristas que compran como negocio.
            // full_name sigue siendo el contacto/persona; business_name es opcional.
            $table->string('business_name')->nullable()->after('client_type');
            $table->string('cuit')->nullable()->after('document_number');
            $table->string('address')->nullable()->after('cuit');

            // Preparado para Fase 13 (Cuenta corriente): limite de credito opcional.
            // Todavia no se aplica ninguna logica de bloqueo sobre este valor.
            $table->decimal('credit_limit', 12, 2)->nullable()->after('address');

            // Descuento por defecto para este cliente, aplicable en Ventas (Fase 11).
            $table->decimal('discount_percentage', 5, 2)->default(0)->after('credit_limit');

            // Preparado para Fase 10 (Listas de precios): sin FK real todavia porque
            // esa tabla no existe hasta esa fase (mismo patron que reference_type/
            // reference_id en stock_movements, Fase 6).
            $table->unsignedBigInteger('price_list_id')->nullable()->after('discount_percentage');

            $table->index('client_type');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'client_type',
                'business_name',
                'cuit',
                'address',
                'credit_limit',
                'discount_percentage',
                'price_list_id',
            ]);
        });
    }
};
