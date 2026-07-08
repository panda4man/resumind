<?php

namespace Tests\Feature;

use App\Filament\Resources\ResumeResource;
use App\Filament\Resources\ResumeResource\Pages\ListResumes;
use App\Filament\Resources\ResumeResource\Pages\ViewResume;
use App\Models\Resume;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
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
        Storage::fake('public');
        Storage::disk('public')->put('resumes/software-engineer.pdf', 'pdf contents');

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
        Storage::fake('public');

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

    public function test_authenticated_users_can_view_resume_pdf_inline(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('resumes/software-engineer.pdf', 'pdf contents');

        $resume = Resume::factory()->create([
            'name' => 'Software Engineer',
            'file_path' => 'resumes/software-engineer.pdf',
        ]);

        $response = $this->get(route('admin.resumes.file', $resume));

        $response
            ->assertSuccessful()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=software-engineer.pdf');

        $this->assertSame('pdf contents', $response->streamedContent());
    }

    public function test_resume_view_route_returns_not_found_when_file_is_missing(): void
    {
        Storage::fake('public');

        $resume = Resume::factory()->create([
            'file_path' => 'resumes/missing.pdf',
        ]);

        $this->get(route('admin.resumes.file', $resume))
            ->assertNotFound();
    }

    public function test_guests_cannot_view_resume_pdf_inline(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('resumes/software-engineer.pdf', 'pdf contents');

        $resume = Resume::factory()->create([
            'file_path' => 'resumes/software-engineer.pdf',
        ]);

        Auth::logout();

        $this->get(route('admin.resumes.file', $resume))
            ->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_resume_view_page_has_view_action_that_opens_resume_pdf_in_a_new_tab(): void
    {
        $resume = Resume::factory()->create();

        Livewire::test(ViewResume::class, [
            'record' => $resume->getRouteKey(),
        ])
            ->assertActionExists('viewPdf')
            ->assertActionHasUrl('viewPdf', route('admin.resumes.file', $resume))
            ->assertActionShouldOpenUrlInNewTab('viewPdf');
    }

    public function test_resumes_table_has_view_action_that_opens_resume_pdf_in_a_new_tab(): void
    {
        $resume = Resume::factory()->create();

        Livewire::test(ListResumes::class)
            ->assertTableActionExists('viewPdf')
            ->assertTableActionHasUrl('viewPdf', route('admin.resumes.file', $resume), $resume)
            ->assertTableActionShouldOpenUrlInNewTab('viewPdf', $resume);
    }
}
