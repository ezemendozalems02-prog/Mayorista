<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arca_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->unique()->constrained()->onDelete('cascade');
            $table->text('certificate')->nullable();
            $table->text('private_key')->nullable();
            $table->string('certificate_alias')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arca_certificates');
    }
};
