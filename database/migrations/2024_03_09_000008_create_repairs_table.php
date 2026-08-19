<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained()->onDelete('set null');
            $table->string('repair_number')->unique();
            $table->string('device_brand')->default('Apple');
            $table->string('device_model');
            $table->string('imei')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('status')->default('pending'); // App\Enums\RepairStatus
            $table->string('priority')->default('medium'); // App\Enums\RepairPriority
            $table->text('reported_issue');
            $table->text('diagnosis')->nullable();
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('final_cost', 15, 2)->default(0);
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->integer('warranty_days')->default(90);
            $table->text('internal_notes')->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('organization_id');
            $table->index('status');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
