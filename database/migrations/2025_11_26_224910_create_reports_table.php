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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // eod, custom, daily, etc.
            $table->date('report_date');
            $table->date('start_date')->nullable(); // For custom date range reports
            $table->date('end_date')->nullable(); // For custom date range reports
            $table->text('highlights')->nullable();
            $table->text('challenges')->nullable();
            $table->json('data')->nullable(); // Store report data as JSON
            $table->string('file_path')->nullable(); // If report is exported to file
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('type');
            $table->index('report_date');
            $table->index(['user_id', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
