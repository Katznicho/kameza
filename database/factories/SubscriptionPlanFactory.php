<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $plans = [
            'A',
            'B',
            'C',
        ];

        return [
            //

            'name' => $this->faker->randomElement($plans),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(100, 1000),
            'status' => $this->faker->boolean(),
            'additional_info' => $this->faker->sentence(),
            'additional_info_amount' => $this->faker->numberBetween(100, 1000),

        ];
    }
}
