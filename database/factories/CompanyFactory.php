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
            'website' => fake()->optional()->url(),
            'glassdoor' => fake()->optional()->url(),
            'stack' => fake()->optional()->randomElement(['PHP/Laravel', 'TypeScript/React', 'Go', 'Python/Django', 'Ruby/Rails', 'Java/Spring']),
        ];
    }
}
