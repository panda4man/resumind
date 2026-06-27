<?php

namespace Tests\Unit\Models;

use App\Enums\StatusEventName;
use App\Models\JobApplication;
use App\Models\JobApplicationStatusEvent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JobApplicationStatusEventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('job_application_status_events')) {
            Schema::create('job_application_status_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
                $table->string('event_name');
                $table->timestamp('occurred_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_status_event_belongs_to_job_application(): void
    {
        $application = JobApplication::factory()->create();
        $statusEvent = JobApplicationStatusEvent::factory()->for($application)->create();

        $this->assertInstanceOf(JobApplication::class, $statusEvent->jobApplication);
        $this->assertEquals($application->id, $statusEvent->jobApplication->id);
    }

    public function test_status_event_casts_enum_and_datetime(): void
    {
        $statusEvent = JobApplicationStatusEvent::factory()->create([
            'event_name' => StatusEventName::Responded,
            'occurred_at' => now()->subDay(),
        ]);

        $this->assertSame(StatusEventName::Responded, $statusEvent->event_name);
        $this->assertIsObject($statusEvent->occurred_at);
    }

    public function test_duplicate_event_names_are_allowed_per_job_application(): void
    {
        $application = JobApplication::factory()->create();

        $firstEvent = JobApplicationStatusEvent::factory()->for($application)->create([
            'event_name' => StatusEventName::Responded,
            'occurred_at' => now()->subDays(2),
        ]);

        $secondEvent = JobApplicationStatusEvent::factory()->for($application)->create([
            'event_name' => StatusEventName::Responded,
            'occurred_at' => now()->subDay(),
        ]);

        $this->assertNotSame($firstEvent->id, $secondEvent->id);
        $this->assertSame(2, $application->statusEvents()->count());
    }

    public function test_same_event_name_can_exist_on_different_job_applications(): void
    {
        $firstApplication = JobApplication::factory()->create();
        $secondApplication = JobApplication::factory()->create();

        $firstEvent = JobApplicationStatusEvent::factory()->for($firstApplication)->create([
            'event_name' => StatusEventName::Responded,
        ]);

        $secondEvent = JobApplicationStatusEvent::factory()->for($secondApplication)->create([
            'event_name' => StatusEventName::Responded,
        ]);

        $this->assertNotEquals($firstEvent->job_application_id, $secondEvent->job_application_id);
    }
}
