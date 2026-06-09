<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'website' => null,
            'glassdoor' => null,
            'stack' => null,
            'summary' => null,
        ];
    }

    public function withWebsite(): static
    {
        return $this->state(['website' => fake()->url()]);
    }
}
