<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arca_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->text('token')->nullable();
            $table->text('sign')->nullable();
            $table->timestamp('expiration')->nullable();
            $table->enum('environment', ['TESTING', 'PRODUCTION'])->default('TESTING');
            $table->timestamps();

            // One active token per organization per environment
            $table->unique(['organization_id', 'environment']);
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arca_tokens');
    }
};
