<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const UNIQUE_INDEX = 'ja_status_events_job_app_event_unique';

    public function up(): void
    {
        if (! Schema::hasTable('job_application_status_events')) {
            Schema::create('job_application_status_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
                $table->string('event_name');
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();

                $table->unique(['job_application_id', 'event_name'], self::UNIQUE_INDEX);
            });

            return;
        }

        if (! Schema::hasIndex('job_application_status_events', self::UNIQUE_INDEX, 'unique')) {
            Schema::table('job_application_status_events', function (Blueprint $table) {
                $table->unique(['job_application_id', 'event_name'], self::UNIQUE_INDEX);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_application_status_events');
    }
};
