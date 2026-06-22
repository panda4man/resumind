<?php

namespace Tests\Feature;

use App\Enums\StatusEventName;
use App\Filament\Resources\JobApplicationResource\Pages\EditJobApplication;
use App\Filament\Resources\JobApplicationResource\RelationManagers\StatusEventsRelationManager;
use App\Models\JobApplication;
use App\Models\JobApplicationStatusEvent;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class StatusEventsRelationManagerTest extends TestCase
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

    public function test_status_events_relation_manager_renders_existing_records(): void
    {
        $application = JobApplication::factory()->create();
        $statusEvent = JobApplicationStatusEvent::factory()->for($application)->create([
            'event_name' => StatusEventName::Interviewing,
        ]);

        Livewire::test(StatusEventsRelationManager::class, [
            'ownerRecord' => $application,
            'pageClass' => EditJobApplication::class,
        ])
            ->assertCanSeeTableRecords([$statusEvent]);
    }

    public function test_status_events_can_be_created_edited_and_deleted(): void
    {
        $application = JobApplication::factory()->create();

        $component = Livewire::test(StatusEventsRelationManager::class, [
            'ownerRecord' => $application,
            'pageClass' => EditJobApplication::class,
        ]);

        $component->callTableAction('create', data: [
            'event_name' => StatusEventName::Submitted->value,
            'occurred_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
        ]);

        $createdEvent = JobApplicationStatusEvent::query()->first();

        $this->assertNotNull($createdEvent);

        $component->callTableAction('edit', $createdEvent, data: [
            'event_name' => StatusEventName::Responded->value,
            'occurred_at' => now()->subDay()->format('Y-m-d H:i:s'),
        ]);

        $createdEvent->refresh();

        $this->assertSame(StatusEventName::Responded, $createdEvent->event_name);

        $component->callTableAction('delete', $createdEvent);

        $this->assertDatabaseMissing('job_application_status_events', [
            'id' => $createdEvent->id,
        ]);
    }
}
