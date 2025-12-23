<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contactType = fake()->randomElement(['company', 'personal']);

        return [
            'contact_type' => $contactType,
            'name' => $contactType === 'personal' ? fake()->name() : null,
            'position' => fake()->jobTitle(),
            'company' => $contactType === 'company' ? fake()->company() : null,
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'source' => fake()->randomElement(['website', 'referral', 'cold_call', 'linkedin', 'trade_show', 'other']),
            'sector' => fake()->randomElement(['technology', 'healthcare', 'finance', 'retail', 'manufacturing', 'other']),
            'added_by' => User::factory(),
            'status' => 'new_lead',
            'value' => fake()->randomFloat(2, 1000, 100000),
            'product' => fake()->words(2, true),
            'expected_close_date' => fake()->dateTimeBetween('now', '+3 months'),
            'is_client' => false,
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the lead is a client (won).
     */
    public function client(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'won',
            'is_client' => true,
            'won_at' => now(),
            'actual_close_date' => now(),
        ]);
    }

    /**
     * Indicate that the lead is won.
     */
    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'won',
            'is_client' => true,
            'won_at' => now(),
            'actual_close_date' => now(),
        ]);
    }

    /**
     * Indicate that the lead is lost.
     */
    public function lost(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'lost',
            'lost_reason' => fake()->sentence(),
            'actual_close_date' => now(),
        ]);
    }

    /**
     * Indicate that the lead is in negotiations.
     */
    public function negotiations(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'negotiations',
        ]);
    }

    /**
     * Indicate that this is a company contact.
     */
    public function company(): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_type' => 'company',
            'name' => null,
            'company' => fake()->company(),
        ]);
    }

    /**
     * Indicate that this is a personal contact.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'contact_type' => 'personal',
            'name' => fake()->name(),
            'company' => null,
        ]);
    }
}
