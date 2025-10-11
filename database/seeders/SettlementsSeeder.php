<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Settlement;

class SettlementsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->count() < 2) return;
 
        Settlement::factory(5)->make()->each(function ($s) use ($users) { 
            $from = $users->random();
            $to = $users->where('id', '<>', $from->id)->random();
            Settlement::create([
                'from_user_id' => $from->id,
                'to_user_id'   => $to->id,
                'amount'       => $s->amount,
                'paid_at'      => $s->paid_at,
                'note'         => $s->note,
            ]);
        });
 
        $marko = User::where('email','marko@budget.test')->first();
        $jovana = User::where('email','jovana@budget.test')->first();
        if ($marko && $jovana) {
            Settlement::firstOrCreate([
                'from_user_id' => $jovana->id,
                'to_user_id'   => $marko->id,
                'amount'       => 2000.00,
                'paid_at'      => now()->subDay(),
            ], [
                'note'         => 'Delo iznosa za namirnice',
            ]);
        }
    }
}
