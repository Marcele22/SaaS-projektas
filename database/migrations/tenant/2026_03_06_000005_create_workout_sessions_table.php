<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_subscription_id')
                ->constrained('customer_subscriptions')
                ->cascadeOnDelete();

            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('activity')->nullable();
            $table->string('trainer_name')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('attended_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['customer_subscription_id', 'session_date', 'start_time'],
                'uniq_subscription_session_slot'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sessions');
    }
};