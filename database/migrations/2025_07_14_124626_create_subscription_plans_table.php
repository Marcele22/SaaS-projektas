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
        Schema::create('subscription_plans', function (Blueprint $table) {
    $table->id();
    $table->uuid('tenant_id')->index();

    $table->string('name');
    $table->decimal('price', 10, 2);
    $table->unsignedInteger('weekly_sessions');
    $table->timestamps();
});

        Schema::create('customer_subscription', function (Blueprint $table) {
    $table->id();

    $table->uuid('tenant_id')->index();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('subscription_plan_id')->constrained('subscription_plans');

    $table->date('start_date');
    $table->date('end_date');

    $table->enum('status', ['pending','active','suspended','cancelled'])
          ->default('pending');

    $table->decimal('payment', 10, 2)->nullable();
    $table->string('stripe_subscription_id')->nullable();
    $table->date('next_billing_date')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
