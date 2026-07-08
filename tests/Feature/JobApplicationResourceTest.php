<?php

namespace Tests\Feature;

use App\Enums\JobApplicationStatusesEnum;
use App\Enums\StatusEventName;
use App\Filament\Resources\JobApplicationResource;
use App\Filament\Resources\JobApplicationResource\Pages\CreateJobApplication;
use App\Filament\Resources\JobApplicationResource\Pages\EditJobApplication;
use App\Filament\Resources\JobApplicationResource\Pages\ListJobApplications;
use App\Filament\Resources\JobApplicationResource\Pages\ViewJobApplication;
use App\Filament\Resources\JobApplicationResource\RelationManagers\CoverLettersRelationManager;
use App\Filament\Resources\JobApplicationResource\RelationManagers\InterviewsRelationManager;
use App\Models\JobApplication;
use App\Models\JobApplicationStatusEvent;
use App\Models\User;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class JobApplicationResourceTest extends TestCase
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

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
    }

    public function test_job_applications_index_renders_for_authenticated_users(): void
    {
        $this->get(JobApplicationResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_job_applications_index_defaults_to_submitted_at_descending(): void
    {
        $oldest = JobApplication::factory()->create([
            'job_title' => 'Oldest application',
            'submitted_at' => now()->subDays(10),
        ]);
        $middle = JobApplication::factory()->create([
            'job_title' => 'Middle application',
            'submitted_at' => now()->subDays(5),
        ]);
        $newest = JobApplication::factory()->create([
            'job_title' => 'Newest application',
            'submitted_at' => now()->subDay(),
        ]);

        Livewire::test(ListJobApplications::class)
            ->assertCanSeeTableRecords([$newest, $middle, $oldest], inOrder: true);
    }

    public function test_create_form_hides_submitted_at_field(): void
    {
        Livewire::test(CreateJobApplication::class)
            ->assertFormFieldDoesNotExist('submitted_at');
    }

    public function test_edit_form_shows_submitted_at_as_readonly_state_from_submitted_event(): void
    {
        $application = JobApplication::factory()->create([
            'submitted_at' => now()->subDays(10),
        ]);

        $submittedEvent = JobApplicationStatusEvent::factory()->for($application)->create([
            'event_name' => StatusEventName::Submitted,
            'occurred_at' => now()->subDays(2)->setTime(15, 45),
        ]);

        Livewire::test(EditJobApplication::class, [
            'record' => $application->getRouteKey(),
        ])
            ->assertFormFieldExists('submitted_at')
            ->assertFormFieldIsReadOnly('submitted_at')
            ->assertFormSet([
                'submitted_at' => $submittedEvent->occurred_at,
            ]);
    }

    public function test_job_application_view_page_renders_for_authenticated_users(): void
    {
        $application = JobApplication::factory()->create();

        $this->get(JobApplicationResource::getUrl('view', ['record' => $application]))
            ->assertSuccessful();
    }

    public function test_view_page_can_advance_status_and_sync_application_status(): void
    {
        $application = JobApplication::factory()->create([
            'status' => JobApplicationStatusesEnum::Prospecting->value,
        ]);

        Livewire::test(ViewJobApplication::class, [
            'record' => $application->getRouteKey(),
        ])
            ->callAction('advanceStatus', data: [
                'event_name' => StatusEventName::Submitted->value,
                'occurred_at' => now()->format('Y-m-d H:i:s'),
            ]);

        $this->assertDatabaseHas('job_application_status_events', [
            'job_application_id' => $application->id,
            'event_name' => StatusEventName::Submitted->value,
        ]);
        $this->assertSame(JobApplicationStatusesEnum::Applied->value, $application->fresh()->status);
    }

    public function test_advance_status_modal_defaults_occurred_at_to_configured_timezone(): void
    {
        config(['app.timezone' => 'America/New_York']);
        Carbon::setTestNow(Carbon::parse('2026-07-08 16:34:56', 'UTC'));

        $application = JobApplication::factory()->create([
            'status' => JobApplicationStatusesEnum::Prospecting->value,
        ]);

        try {
            Livewire::test(ViewJobApplication::class, [
                'record' => $application->getRouteKey(),
            ])
                ->mountAction('advanceStatus')
                ->assertActionDataSet([
                    'occurred_at' => '2026-07-08 12:34:56',
                ]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_view_page_status_timeline_is_ordered_by_occurred_at_descending(): void
    {
        $application = JobApplication::factory()->create();
        $newestOccurredAt = now()->subDay()->setTime(15, 0);
        $middleOccurredAt = now()->subDays(2)->setTime(11, 0);
        $oldestOccurredAt = now()->subDays(3)->setTime(9, 0);

        JobApplicationStatusEvent::factory()->for($application)->create([
            'event_name' => StatusEventName::Interviewing,
            'occurred_at' => $newestOccurredAt,
        ]);
        JobApplicationStatusEvent::factory()->for($application)->create([
            'event_name' => StatusEventName::Submitted,
            'occurred_at' => $oldestOccurredAt,
        ]);
        JobApplicationStatusEvent::factory()->for($application)->create([
            'event_name' => StatusEventName::Responded,
            'occurred_at' => $middleOccurredAt,
        ]);

        $response = $this->get(JobApplicationResource::getUrl('view', ['record' => $application]));

        $response->assertSuccessful();
        $response->assertSeeInOrder([
            $newestOccurredAt->format('M d, Y H:i'),
            $middleOccurredAt->format('M d, Y H:i'),
            $oldestOccurredAt->format('M d, Y H:i'),
        ]);
    }

    public function test_job_application_resource_excludes_status_events_relation_manager(): void
    {
        $this->assertSame([
            InterviewsRelationManager::class,
            CoverLettersRelationManager::class,
        ], JobApplicationResource::getRelations());
    }
}
