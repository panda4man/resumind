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
        Schema::table('job_applications', function (Blueprint $table) {
            $table->boolean('preferred')->default(false)->after('status');
            $table->unsignedSmallInteger('salary_lower')->nullable()->after('preferred');
            $table->unsignedSmallInteger('salary_upper')->nullable()->after('salary_lower');
            $table->string('website')->nullable()->after('salary_upper');
            $table->string('glassdoor')->nullable()->after('website');
            $table->string('stack')->nullable()->after('glassdoor');
            $table->boolean('remote')->default(false)->after('stack');
            $table->string('source')->nullable()->after('remote');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn([
                'preferred', 'salary_lower', 'salary_upper', 'website',
                'glassdoor', 'stack', 'remote', 'source',
            ]);
        });
    }
};
