<?php

namespace Database\Factories;

use App\Models\Interview;
use App\Models\JobApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interview>
 */
class InterviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_application_id' => JobApplication::factory(),
            'name' => fake()->randomElement(['Initial Screening', 'Technical Round', 'Final Round', 'CEO Chat']),
            'interview_date' => fake()->dateTimeBetween('+1 day', '+1 month'),
            'type' => fake()->randomElement(['Technical', 'Behavioral', 'Panel', 'HR', 'CEO Chat']),
            'format' => fake()->randomElement(['Zoom', 'Phone', 'In-Person', 'Google Meet']),
            'length_minutes' => fake()->randomElement([30, 45, 60, 75, 90, 120]),
            'notes' => fake()->optional(0.7)->paragraph(),
        ];
    }
}
