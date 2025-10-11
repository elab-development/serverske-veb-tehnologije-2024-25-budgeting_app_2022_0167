<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        
        User::factory(5)->create();

      
        User::query()->firstOrCreate(
            ['email' => 'admin@budget.test'],
            [
                'name' => 'Admin Budget',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'marko@budget.test'],
            [
                'name' => 'Marko Markovic',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
        User::query()->firstOrCreate(
            ['email' => 'jovana@budget.test'],
            [
                'name' => 'Jovana Jovic',
                'password' => Hash::make('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
        User::query()->firstOrCreate(
            ['email' => 'djurdja@budget.test'],
            [
                'name' => 'Djurdja Vidanovic',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
