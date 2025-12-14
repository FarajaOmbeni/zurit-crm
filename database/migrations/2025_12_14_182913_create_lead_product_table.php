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
        Schema::create('lead_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('product_name')->nullable(); // Store product name if product_id is null (for legacy data)
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('lead_id');
            $table->index('product_id');
            // Note: Duplicate prevention is handled in application logic since product_id can be null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_product');
    }
};
