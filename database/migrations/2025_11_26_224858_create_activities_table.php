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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('type', ['call', 'email', 'meeting', 'note']);
            $table->datetime('activity_date');
            $table->text('description')->nullable();
            $table->text('outcome')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('lead_id');
            $table->index('user_id');
            $table->index('activity_date');
            $table->index('type');
            $table->index(['lead_id', 'activity_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
