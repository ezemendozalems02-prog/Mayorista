<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * clients.price_list_id se agrego en Fase 9 (2026_08_19_000010) como columna
     * simple, sin FK, porque price_lists todavia no existia. Ahora que existe
     * (Fase 10), completamos la referencia real.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreign('price_list_id')->references('id')->on('price_lists')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['price_list_id']);
        });
    }
};
