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
        Schema::table('lead_product', function (Blueprint $table) {
            // Pipeline status for this lead-product combination
            $table->enum('status', ['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost'])
                ->nullable()
                ->after('product_name');

            // Notes specific to this lead-product combination
            $table->text('notes')->nullable()->after('status');

            // Deal value for this specific product
            $table->decimal('value', 15, 2)->nullable()->after('notes');

            // Expected close date for this product
            $table->date('expected_close_date')->nullable()->after('value');

            // Actual close date for this product
            $table->date('actual_close_date')->nullable()->after('expected_close_date');

            // Timestamp when this product was won
            $table->timestamp('won_at')->nullable()->after('actual_close_date');

            // Reason if this product deal was lost
            $table->text('lost_reason')->nullable()->after('won_at');

            // Indexes for better query performance
            $table->index('status');
            $table->index(['lead_id', 'product_id']);
            $table->index(['product_id', 'status']);
            $table->index('expected_close_date');
            $table->index('won_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_product', function (Blueprint $table) {
            // Drop indexes first
            // Note: Laravel automatically generates index names, so we drop by column names
            $table->dropIndex(['status']);
            $table->dropIndex(['lead_id', 'product_id']);
            $table->dropIndex(['product_id', 'status']);
            $table->dropIndex(['expected_close_date']);
            $table->dropIndex(['won_at']);

            // Drop columns
            $table->dropColumn([
                'status',
                'notes',
                'value',
                'expected_close_date',
                'actual_close_date',
                'won_at',
                'lost_reason',
            ]);
        });
    }
};
