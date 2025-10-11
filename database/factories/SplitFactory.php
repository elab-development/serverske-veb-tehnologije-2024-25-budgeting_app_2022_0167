<?php

namespace Database\Factories;

use App\Models\Split;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SplitFactory extends Factory
{
    protected $model = Split::class;

    public function definition(): array
    {
        return [
            'expense_id' => Expense::factory(),
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 5000),
            'settled_at' => $this->faker->optional(0.2)->dateTimeBetween('-30 days','now'),
        ];
    }
}
