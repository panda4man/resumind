<?php

namespace Database\Factories;

use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoverLetter>
 */
class CoverLetterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'job_application_id' => JobApplication::factory(),
            'name' => fake()->jobTitle() . ' Cover Letter',
            'file_path' => '/path/to/cover-letter-' . fake()->uuid() . '.pdf',
        ];
    }
}
