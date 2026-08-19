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
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('affiliate_code')->unique();
            $table->string('type'); // client_referral, external, partner
            $table->decimal('balance', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->constrained()->onDelete('set null');
            $table->string('status')->default('registered'); // registered, subscribed, cancelled
            $table->timestamps();
        });

        Schema::create('commission_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // percentage, fixed, credit
            $table->decimal('amount', 10, 2);
            $table->boolean('is_recurring')->default(false);
            $table->string('trigger_event'); // new_subscription, renewal, registration
            $table->timestamps();
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->onDelete('cascade');
            $table->foreignId('referral_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, paid, rejected
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('commission_rules');
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('affiliates');
    }
};
