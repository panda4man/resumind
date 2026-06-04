<?php

namespace Tests\Unit\Models;

use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\Resume;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_interview_belongs_to_job_application(): void
    {
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->create();
        $interview = Interview::factory()->for($application)->create();

        $this->assertInstanceOf(JobApplication::class, $interview->jobApplication);
        $this->assertEquals($application->id, $interview->jobApplication->id);
    }

    public function test_interview_fillable_attributes(): void
    {
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->create();

        $interview = Interview::create([
            'job_application_id' => $application->id,
            'name' => 'Initial Screening',
            'interview_date' => now()->addDays(7),
            'type' => 'Technical',
            'format' => 'Zoom',
            'length_minutes' => 60,
            'notes' => 'Prepare for algorithm questions',
        ]);

        $this->assertEquals('Initial Screening', $interview->name);
        $this->assertEquals('Technical', $interview->type);
        $this->assertEquals('Zoom', $interview->format);
        $this->assertEquals(60, $interview->length_minutes);
        $this->assertEquals('Prepare for algorithm questions', $interview->notes);
    }

    public function test_interview_casts_datetime(): void
    {
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->create();
        $interview = Interview::factory()->for($application)->create();

        $this->assertIsObject($interview->interview_date);
    }

    public function test_interview_length_human_readable_attribute(): void
    {
        $resume = Resume::factory()->create();
        $application = JobApplication::factory()->for($resume)->create();
        $interview = Interview::factory()->for($application)->create(['length_minutes' => 75]);

        $this->assertEquals('1h 15m', $interview->length_human_readable);
    }
}
