<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
       
        Category::factory(5)->create();

       
        foreach (['Hrana', 'Stanarina', 'Komunalije', 'Prevoz', 'Putovanja'] as $name) {
            Category::query()->firstOrCreate(['name' => $name]);
        }
    }
}
