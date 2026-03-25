<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Suggestion>
 */
class SuggestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'keywords' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'url' => fake()->url(),
            'locale' => fake()->randomElement(['en', 'it']),
            'sorting' => fake()->numberBetween(1, 100),
        ];
    }
}
