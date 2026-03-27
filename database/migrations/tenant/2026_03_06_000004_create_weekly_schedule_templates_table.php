<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_schedule_templates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_subscription_id')
                ->constrained('customer_subscriptions')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('activity')->nullable();
            $table->string('trainer_name')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_schedule_templates');
    }
};