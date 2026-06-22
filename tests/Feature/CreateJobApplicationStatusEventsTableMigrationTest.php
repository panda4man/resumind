<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CreateJobApplicationStatusEventsTableMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('job_application_status_events');
        Schema::dropIfExists('job_applications');
        Schema::enableForeignKeyConstraints();

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
        });
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('job_application_status_events');
        Schema::dropIfExists('job_applications');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_migration_creates_table_on_a_fresh_database(): void
    {
        $migration = require base_path('database/migrations/2026_06_20_120000_create_job_application_status_events_table.php');

        $migration->up();

        $this->assertTrue(Schema::hasTable('job_application_status_events'));
        $this->assertTrue(
            Schema::hasIndex('job_application_status_events', ['job_application_id', 'event_name'], 'unique')
        );
    }

    public function test_migration_is_safe_when_the_table_already_exists(): void
    {
        Schema::create('job_application_status_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->string('event_name');
            $table->timestamp('occurred_at')->nullable();
            $table->timestamps();
        });

        $migration = require base_path('database/migrations/2026_06_20_120000_create_job_application_status_events_table.php');

        $migration->up();

        $this->assertTrue(Schema::hasTable('job_application_status_events'));
        $this->assertTrue(
            Schema::hasIndex('job_application_status_events', ['job_application_id', 'event_name'], 'unique')
        );
    }
}
