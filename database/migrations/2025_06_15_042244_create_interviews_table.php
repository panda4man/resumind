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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->dateTime('interview_date')->nullable();
            $table->string('type'); // What the interview was (e.g. Technical, Panel, CEO Chat)
            $table->string('format'); // How the interview was conducted (e.g. Zoom, Phone, In-Person)
            $table->integer('length_minutes')->nullable(); // length of interview
            $table->text('notes')->nullable(); // Optional summary or questions asked
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
