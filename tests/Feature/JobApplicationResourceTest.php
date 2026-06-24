<?php

namespace Tests\Feature;

use App\Enums\StatusEventName;
use App\Filament\Resources\JobApplicationResource\Pages\CreateJobApplication;
use App\Filament\Resources\JobApplicationResource\Pages\EditJobApplication;
use App\Filament\Resources\JobApplicationResource;
use App\Filament\Resources\JobApplicationResource\Pages\ListJobApplications;
use App\Models\JobApplication;
use App\Models\JobApplicationStatusEvent;
use App\Models\User;
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
                $table->unique(['job_application_id', 'event_name']);
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
}
