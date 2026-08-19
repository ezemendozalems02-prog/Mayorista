<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Una lista solo guarda las excepciones/precios especiales: un producto que
        // no esta en la lista se resuelve con su retail_price/wholesale_price base
        // (ver App\Services\PriceResolverService). Asi una lista no necesita repetir
        // el catalogo entero.
        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['price_list_id', 'product_id']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
    }
};
