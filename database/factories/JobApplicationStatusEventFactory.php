<?php

namespace Database\Factories;

use App\Enums\StatusEventName;
use App\Models\JobApplication;
use App\Models\JobApplicationStatusEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JobApplicationStatusEvent>
 */
class JobApplicationStatusEventFactory extends Factory
{
    protected $model = JobApplicationStatusEvent::class;

    public function definition(): array
    {
        return [
            'job_application_id' => JobApplication::factory(),
            'event_name' => fake()->randomElement(StatusEventName::cases()),
            'occurred_at' => fake()->dateTimeBetween('-3 months'),
        ];
    }
}
