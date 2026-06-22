<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_application_status_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->string('event_name');
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();

            $table->unique(['job_application_id', 'event_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_application_status_events');
    }
};
