<?php

namespace Tests\Feature;

use App\Enums\StatusEventName;
use App\Models\JobApplication;
use App\Models\JobApplicationStatusEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackfillSubmittedStatusEventsCommandTest extends TestCase
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
                $table->unique(['job_application_id', 'event_name']);
            });
        }
    }

    public function test_it_backfills_missing_submitted_status_events_from_legacy_submitted_at_values(): void
    {
        $legacySubmittedAt = now()->subDays(3)->startOfMinute();

        $needsSubmittedEvent = JobApplication::factory()->create([
            'submitted_at' => $legacySubmittedAt,
        ]);

        $alreadySubmitted = JobApplication::factory()->create([
            'submitted_at' => now()->subDays(2)->startOfMinute(),
        ]);
        JobApplicationStatusEvent::factory()->for($alreadySubmitted)->create([
            'event_name' => StatusEventName::Submitted,
            'occurred_at' => $alreadySubmitted->submitted_at,
        ]);

        $hasOtherEventOnly = JobApplication::factory()->create([
            'submitted_at' => now()->subDay()->startOfMinute(),
        ]);
        JobApplicationStatusEvent::factory()->for($hasOtherEventOnly)->create([
            'event_name' => StatusEventName::Responded,
            'occurred_at' => $hasOtherEventOnly->submitted_at->copy()->addDay(),
        ]);

        $missingLegacyTimestamp = JobApplication::factory()->create([
            'submitted_at' => null,
        ]);

        $this->artisan('job-applications:backfill-submitted-status-events')
            ->expectsOutputToContain('Created 2 submitted status events')
            ->expectsOutputToContain('Skipped 1 applications that already had submitted events')
            ->expectsOutputToContain('Ignored 1 applications without submitted_at')
            ->assertExitCode(0);

        $this->assertDatabaseHas('job_application_status_events', [
            'job_application_id' => $needsSubmittedEvent->id,
            'event_name' => StatusEventName::Submitted->value,
            'occurred_at' => $legacySubmittedAt,
        ]);

        $this->assertDatabaseHas('job_application_status_events', [
            'job_application_id' => $hasOtherEventOnly->id,
            'event_name' => StatusEventName::Submitted->value,
            'occurred_at' => $hasOtherEventOnly->submitted_at,
        ]);

        $this->assertDatabaseCount('job_application_status_events', 4);

        $this->assertDatabaseMissing('job_application_status_events', [
            'job_application_id' => $missingLegacyTimestamp->id,
            'event_name' => StatusEventName::Submitted->value,
        ]);
    }

    public function test_it_is_safe_to_rerun_without_creating_duplicate_submitted_events(): void
    {
        $application = JobApplication::factory()->create([
            'submitted_at' => now()->subWeek()->startOfMinute(),
        ]);

        $this->artisan('job-applications:backfill-submitted-status-events')
            ->assertExitCode(0);

        $this->artisan('job-applications:backfill-submitted-status-events')
            ->expectsOutputToContain('Created 0 submitted status events')
            ->expectsOutputToContain('Skipped 1 applications that already had submitted events')
            ->assertExitCode(0);

        $this->assertSame(
            1,
            JobApplicationStatusEvent::query()
                ->where('job_application_id', $application->id)
                ->where('event_name', StatusEventName::Submitted->value)
                ->count()
        );
    }
}
