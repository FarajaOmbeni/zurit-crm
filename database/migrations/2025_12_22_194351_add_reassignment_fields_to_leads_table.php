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
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('original_added_by')->nullable()->after('added_by')->constrained('users')->nullOnDelete();
            $table->foreignId('reassigned_by')->nullable()->after('original_added_by')->constrained('users')->nullOnDelete();
            $table->timestamp('reassigned_at')->nullable()->after('reassigned_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['original_added_by']);
            $table->dropForeign(['reassigned_by']);
            $table->dropColumn(['original_added_by', 'reassigned_by', 'reassigned_at']);
        });
    }
};
