<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_name' => fake()->company(),
            'ownership_type' => fake()->randomElement(['Company Owned', 'Licensed']),
            'street_address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'postcode' => fake()->postcode(),
            'geocoded' => false,
        ];
    }

    public function geocoded(
        ?int $medianHouseholdIncome = null,
        ?int $perCapitaIncome = null,
        ?float $collegeDegreePct = null,
        ?float $medianAge = null,
    ): static {
        return $this->state(fn () => [
            'geocoded' => true,
            'median_household_income' => $medianHouseholdIncome ?? fake()->numberBetween(30000, 150000),
            'per_capita_income' => $perCapitaIncome ?? fake()->numberBetween(15000, 80000),
            'college_degree_pct' => $collegeDegreePct ?? fake()->randomFloat(2, 5, 75),
            'median_age' => $medianAge ?? fake()->randomFloat(1, 20, 65),
        ]);
    }

    public function companyOwned(): static
    {
        return $this->state(fn () => ['ownership_type' => 'Company Owned']);
    }

    public function licensed(): static
    {
        return $this->state(fn () => ['ownership_type' => 'Licensed']);
    }
}
