<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'website', 'glassdoor', 'stack']);
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('company_id');
            $table->string('website')->nullable();
            $table->string('glassdoor')->nullable();
            $table->string('stack')->nullable();
        });
    }
};
