<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arca_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('endpoint');
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arca_logs');
    }
};
