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
        Schema::create('follow_up_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained('tasks')->onDelete('set null');
            $table->enum('type', ['initial_email', 'follow_up_email', 'call']);
            $table->datetime('scheduled_at');
            $table->datetime('completed_at')->nullable();
            $table->integer('interval_days')->nullable(); // For recurring: 2 days, 7 days
            $table->boolean('is_recurring')->default(false);
            $table->datetime('next_follow_up_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('lead_id');
            $table->index('task_id');
            $table->index('scheduled_at');
            $table->index('next_follow_up_date');
            $table->index('is_recurring');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_up_schedules');
    }
};
