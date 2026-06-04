<?php

namespace Tests\Unit\Models;

use App\Models\JobApplication;
use App\Models\Resume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResumeTest extends TestCase
{
    use RefreshDatabase;

    public function test_resume_has_many_job_applications(): void
    {
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->create();

        $this->assertTrue($resume->jobApplications->contains($application));
        $this->assertEquals(1, $resume->jobApplications()->count());
    }

    public function test_resume_fillable_attributes(): void
    {
        $resume = Resume::create([
            'name' => 'Senior Developer Resume',
            'file_path' => '/path/to/resume.pdf',
        ]);

        $this->assertEquals('Senior Developer Resume', $resume->name);
        $this->assertEquals('/path/to/resume.pdf', $resume->file_path);
    }
}
