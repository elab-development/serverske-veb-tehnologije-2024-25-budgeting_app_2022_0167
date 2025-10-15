<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'paid_by' => User::factory(),
            'category_id' => Category::factory(),
            'paid_at' => $this->faker->dateTimeBetween('-60 days', 'now'),
            'amount' => $this->faker->randomFloat(2, 300, 15000),
            'description' => $this->faker->optional()->sentence(6),
        ];
    }
}
