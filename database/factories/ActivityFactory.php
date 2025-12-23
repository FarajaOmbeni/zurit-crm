<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['call', 'email', 'meeting', 'note']),
            'description' => fake()->paragraph(),
            'outcome' => fake()->optional()->sentence(),
            'next_action' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the activity is a call.
     */
    public function call(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'call',
        ]);
    }

    /**
     * Indicate that the activity is an email.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'email',
        ]);
    }

    /**
     * Indicate that the activity is a meeting.
     */
    public function meeting(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'meeting',
        ]);
    }
}
