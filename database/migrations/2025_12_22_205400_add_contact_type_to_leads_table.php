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
            // Add contact_type field: 'company' for B2B, 'personal' for individual contacts
            $table->enum('contact_type', ['company', 'personal'])->default('company')->after('id');

            // Make company nullable (for personal contacts, name will be stored here)
            $table->string('company')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('contact_type');
            $table->string('company')->nullable(false)->change();
        });
    }
};
