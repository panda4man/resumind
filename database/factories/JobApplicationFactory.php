<?php

namespace Database\Factories;

use App\Enums\JobApplicationStatusesEnum;
use App\Models\Company;
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
            'status' => JobApplicationStatusesEnum::Prospecting->value,
            'submitted_at' => fake()->dateTimeBetween('-3 months'),
            'responded_at' => fake()->optional(0.7)->dateTimeBetween('-2 months'),
        ];
    }
}
