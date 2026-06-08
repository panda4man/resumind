<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\JobApplication;
use App\Models\Resume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplication>
 */
class JobApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resume_id' => Resume::factory(),
            'company_id' => Company::factory(),
            'job_title' => fake()->jobTitle(),
            'job_description' => fake()->paragraph(),
            'cover_letter_path' => '/path/to/cover-letter-' . fake()->uuid() . '.pdf',
            'status' => fake()->randomElement(['applied', 'interviewing', 'rejected', 'offer', 'accepted']),
            'submitted_at' => fake()->dateTimeBetween('-3 months'),
            'responded_at' => fake()->optional(0.7)->dateTimeBetween('-2 months'),
        ];
    }
}
