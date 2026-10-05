<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store the front and back photos of a visiting card.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('card_front')->nullable()->after('notes');
            $table->string('card_back')->nullable()->after('card_front');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['card_front', 'card_back']);
        });
    }
};
