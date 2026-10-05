<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per contact. Email uniqueness per country is checked in
     * the form request, not here, so a soft-deleted lead can free the email.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 120);
            $table->string('phone_country_code', 8);
            $table->string('phone_number', 20);
            $table->string('email', 190);
            $table->string('company_name', 150);
            $table->string('address', 500)->nullable();
            $table->string('city', 100);
            $table->foreignId('country_id')->constrained()->restrictOnDelete();
            $table->foreignId('lead_status_id')->nullable()->constrained('lead_statuses')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('city');
            $table->index('email');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
