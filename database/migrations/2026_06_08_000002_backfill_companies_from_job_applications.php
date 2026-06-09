<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        // Backfill: one Company per distinct company_name, first non-null wins for website/glassdoor/stack.
        DB::table('job_applications')
            ->select('company_name')
            ->distinct()
            ->whereNotNull('company_name')
            ->orderBy('company_name')
            ->each(function ($row) {
                $sources = DB::table('job_applications')
                    ->where('company_name', $row->company_name)
                    ->orderBy('id')
                    ->get(['website', 'glassdoor', 'stack']);

                $companyId = DB::table('companies')->insertGetId([
                    'name' => $row->company_name,
                    'website' => $sources->first(fn ($s) => $s->website !== null)?->website,
                    'glassdoor' => $sources->first(fn ($s) => $s->glassdoor !== null)?->glassdoor,
                    'stack' => $sources->first(fn ($s) => $s->stack !== null)?->stack,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('job_applications')
                    ->where('company_name', $row->company_name)
                    ->update(['company_id' => $companyId]);
            });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
