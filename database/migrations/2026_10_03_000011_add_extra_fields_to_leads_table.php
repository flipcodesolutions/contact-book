<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Optional contact details: website, notes, dates, work info, and source.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('website', 190)->nullable()->after('email');
            $table->text('notes')->nullable()->after('address');
            $table->date('birthday')->nullable()->after('city');
            $table->date('anniversary')->nullable()->after('birthday');
            $table->string('other_date_label', 100)->nullable()->after('anniversary');
            $table->date('other_date')->nullable()->after('other_date_label');
            $table->string('job_title', 120)->nullable()->after('company_name');
            $table->string('work_department', 120)->nullable()->after('job_title');
            $table->foreignId('lead_source_id')->nullable()->after('lead_status_id')->constrained('lead_sources')->nullOnDelete();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_source_id');
            $table->dropColumn([
                'website',
                'notes',
                'birthday',
                'anniversary',
                'other_date_label',
                'other_date',
                'job_title',
                'work_department',
            ]);
        });
    }
};
