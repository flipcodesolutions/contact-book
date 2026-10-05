<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Only the name, email, and phone are required on a lead.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('company_name', 150)->nullable()->change();
            $table->string('city', 100)->nullable()->change();
            $table->unsignedBigInteger('country_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('company_name', 150)->nullable(false)->change();
            $table->string('city', 100)->nullable(false)->change();
            $table->unsignedBigInteger('country_id')->nullable(false)->change();
        });
    }
};
