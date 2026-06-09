<?php

namespace Tests\Unit\Models;

use App\Models\CoverLetter;
use App\Models\JobApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoverLetterTest extends TestCase
{
    use RefreshDatabase;

    public function test_cover_letter_belongs_to_job_application(): void
    {
        $application = JobApplication::factory()->create();
        $coverLetter = CoverLetter::factory()->for($application)->create();

        $this->assertInstanceOf(JobApplication::class, $coverLetter->jobApplication);
        $this->assertEquals($application->id, $coverLetter->jobApplication->id);
    }

    public function test_cover_letter_fillable_attributes(): void
    {
        $application = JobApplication::factory()->create();

        $coverLetter = CoverLetter::create([
            'job_application_id' => $application->id,
            'name' => 'Senior Developer Cover Letter',
            'file_path' => '/path/to/cover-letter.pdf',
        ]);

        $this->assertEquals('Senior Developer Cover Letter', $coverLetter->name);
        $this->assertEquals('/path/to/cover-letter.pdf', $coverLetter->file_path);
        $this->assertEquals($application->id, $coverLetter->job_application_id);
    }
}
