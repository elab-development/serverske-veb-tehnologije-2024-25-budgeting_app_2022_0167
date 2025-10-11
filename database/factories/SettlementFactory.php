<?php

namespace Database\Factories;

use App\Models\Settlement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettlementFactory extends Factory
{
    protected $model = Settlement::class;

    public function definition(): array
    {
        return [
            'from_user_id' => User::factory(),
            'to_user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 200, 7000),
            'paid_at' => $this->faker->dateTimeBetween('-30 days','now'),
            'note' => $this->faker->optional()->sentence(5),
        ];
    }
}
