<?php

namespace Tests\Unit\Models;

use App\Models\ApplicationQuestion;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\Resume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobApplicationTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_job_application_belongs_to_resume(): void
    {
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->create();

        $this->assertInstanceOf(Resume::class, $application->resume);
        $this->assertEquals($resume->id, $application->resume->id);
    }

    public function test_job_application_fillable_attributes(): void
    {
        $resume = Resume::factory()->create();

        $application = JobApplication::create([
            'resume_id' => $resume->id,
            'company_name' => 'Tech Corp',
            'job_title' => 'Senior Developer',
            'job_description' => 'Build cool stuff',
            'cover_letter_path' => '/path/to/letter.pdf',
            'status' => 'applied',
            'submitted_at' => now(),
            'responded_at' => now(),
        ]);

        $this->assertEquals('Tech Corp', $application->company_name);
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
