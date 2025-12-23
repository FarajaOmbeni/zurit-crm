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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Contact person name
            $table->string('position')->nullable(); // Contact person's position
            $table->string('company'); // Company name
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->foreignId('added_by')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost'])->default('new_lead');
            $table->decimal('value', 15, 2)->nullable(); // Deal value
            $table->string('product')->nullable(); // Product name or reference
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            $table->text('lost_reason')->nullable();
            $table->timestamp('won_at')->nullable(); // When converted to client
            $table->boolean('is_client')->default(false); // Set to true when status = 'won'
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('added_by');
            $table->index('status');
            $table->index('is_client');
            $table->index('email');
            $table->index('phone');
            $table->index('expected_close_date');
            $table->index('won_at');
            $table->index(['added_by', 'status']);
            $table->index(['is_client', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
