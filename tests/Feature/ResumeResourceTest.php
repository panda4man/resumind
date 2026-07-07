<?php

namespace Tests\Feature;

use App\Filament\Resources\ResumeResource;
use App\Filament\Resources\ResumeResource\Pages\ViewResume;
use App\Models\Resume;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ResumeResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
    }

    public function test_resumes_index_renders_for_authenticated_users(): void
    {
        $this->get(ResumeResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_resume_view_page_renders_for_authenticated_users(): void
    {
        $resume = Resume::factory()->create();

        $this->get(ResumeResource::getUrl('view', ['record' => $resume]))
            ->assertSuccessful()
            ->assertSee($resume->name);
    }

    public function test_resume_view_page_download_action_downloads_resume_pdf(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('resumes/software-engineer.pdf', 'pdf contents');

        $resume = Resume::factory()->create([
            'name' => 'Software Engineer',
            'file_path' => 'resumes/software-engineer.pdf',
        ]);

        Livewire::test(ViewResume::class, [
            'record' => $resume->getRouteKey(),
        ])
            ->callAction('download')
            ->assertFileDownloaded('software-engineer.pdf', 'pdf contents', 'application/pdf');
    }

    public function test_resume_view_page_download_action_notifies_when_file_is_missing(): void
    {
        Storage::fake('local');

        $resume = Resume::factory()->create([
            'file_path' => 'resumes/missing.pdf',
        ]);

        Livewire::test(ViewResume::class, [
            'record' => $resume->getRouteKey(),
        ])
            ->callAction('download')
            ->assertNoFileDownloaded()
            ->assertNotified('Resume file not found');
    }
}
