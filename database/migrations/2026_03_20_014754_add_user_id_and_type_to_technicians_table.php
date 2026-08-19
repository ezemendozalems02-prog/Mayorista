<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('organization_id')->constrained()->onDelete('set null');
            $table->string('type')->default('technician')->after('email'); // 'technician', 'seller', 'manager', 'owner'
        });
    }

    public function down(): void
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'type']);
        });
    }
};
