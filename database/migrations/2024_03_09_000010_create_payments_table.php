<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('repair_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->string('type')->default('income'); // income, expense
            $table->string('method')->default('cash'); // cash, transfer, card, crypto, other
            $table->string('currency', 3)->default('USD');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('exchange_rate', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
