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
        Schema::create('workout_sessions', function (Blueprint $table) {
    $table->id();

    $table->uuid('tenant_id')->index();

    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('customer_subscription_id')
          ->constrained('customer_subscription')
          ->cascadeOnDelete();

    $table->date('date');
    $table->time('start_time');
    $table->time('end_time');

    $table->enum('status', ['active','cancelled','completed'])
          ->default('active');

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workout_session');
    }
};
