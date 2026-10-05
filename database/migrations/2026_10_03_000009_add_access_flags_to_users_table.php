<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * All departments means every department in every country.
     * All countries means every department in every country.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('all_departments')->default(false)->after('is_active');
            $table->boolean('all_countries')->default(false)->after('all_departments');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['all_departments', 'all_countries']);
        });
    }
};
