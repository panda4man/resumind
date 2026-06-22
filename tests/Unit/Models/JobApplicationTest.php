<?php

namespace Tests\Unit\Models;

use App\Models\ApplicationQuestion;
use App\Models\Company;
use App\Models\CoverLetter;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobApplicationStatusEvent;
use App\Models\Resume;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class JobApplicationTest extends TestCase
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

    public function test_job_application_has_many_interviews(): void
    {
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->create();
        $interview = Interview::factory()->for($application)->create();

        $this->assertTrue($application->interviews->contains($interview));
        $this->assertEquals(1, $application->interviews()->count());
    }

    public function test_job_application_has_many_questions(): void
    {
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->create();
        $question = ApplicationQuestion::factory()->for($application)->create();

        $this->assertTrue($application->questions->contains($question));
        $this->assertEquals(1, $application->questions()->count());
    }

    public function test_job_application_has_many_cover_letters(): void
    {
        $application = JobApplication::factory()->create();
        $coverLetter = CoverLetter::factory()->for($application)->create();

        $this->assertTrue($application->coverLetters->contains($coverLetter));
        $this->assertEquals(1, $application->coverLetters()->count());
    }

    public function test_job_application_has_many_status_events(): void
    {
        $application = JobApplication::factory()->create();
        $statusEvent = JobApplicationStatusEvent::factory()->for($application)->create();

        $this->assertTrue($application->statusEvents->contains($statusEvent));
        $this->assertEquals(1, $application->statusEvents()->count());
    }

    public function test_job_application_belongs_to_resume(): void
    {
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->create();

        $this->assertInstanceOf(Resume::class, $application->resume);
        $this->assertEquals($resume->id, $application->resume->id);
    }

    public function test_job_application_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->for($company)->create();

        $this->assertInstanceOf(Company::class, $application->company);
        $this->assertEquals($company->id, $application->company->id);
    }

    public function test_job_application_fillable_attributes(): void
    {
        $company = Company::factory()->create();
        $resume = Resume::factory()->create();

        $application = JobApplication::create([
            'resume_id' => $resume->id,
            'company_id' => $company->id,
            'job_title' => 'Senior Developer',
            'job_description' => 'Build cool stuff',
            'status' => 'applied',
            'submitted_at' => now(),
            'responded_at' => now(),
        ]);

        $this->assertEquals($company->id, $application->company_id);
        $this->assertEquals('Senior Developer', $application->job_title);
        $this->assertEquals('Build cool stuff', $application->job_description);
        $this->assertEquals('applied', $application->status);
    }

    public function test_job_application_casts_datetime(): void
    {
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->create();

        $this->assertIsObject($application->submitted_at);
        if ($application->responded_at) {
            $this->assertIsObject($application->responded_at);
        }
    }
}
