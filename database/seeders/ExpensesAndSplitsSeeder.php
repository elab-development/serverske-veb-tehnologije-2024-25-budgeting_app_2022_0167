<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Split;
use Illuminate\Support\Arr;

class ExpensesAndSplitsSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->inRandomOrder()->take(5)->get();
        $categories = Category::query()->pluck('id')->all();

        // 1) Nekoliko nasumičnih troškova preko factory-ja
        Expense::factory(10)->create()->each(function (Expense $expense) use ($users) {
            // učesnici: 2-4 korisnika, uključujući po pravilu nekog ko NIJE platio
            $participants = $users->shuffle()->take(rand(2, 4))->values();

            // ako je payer u učesnicima, neka dug bude raspodela na OSTALe (payer ne duguje samom sebi)
            $others = $participants->reject(fn($u) => $u->id === $expense->paid_by)->values();
            if ($others->isEmpty()) {
                // ako baš svi učesnici == payer (retko), dodaj još jednog iz users kolekcije
                $extra = User::query()->where('id', '<>', $expense->paid_by)->inRandomOrder()->first();
                if ($extra) $others = collect([$extra]);
            }

            $perHead = round($expense->amount / max(1, $others->count()), 2);
            $leftover = $expense->amount - ($perHead * $others->count());

            foreach ($others as $idx => $u) {
                $amount = $perHead + ($idx === 0 ? $leftover : 0); // koriguj centove
                Split::create([
                    'expense_id' => $expense->id,
                    'user_id'    => $u->id,
                    'amount'     => $amount,
                    'settled_at' => null,
                ]);
            }
        });

        // 2) Ručni primer troška sa kontrolisanom podelom
        $payer = User::where('email', 'marko@budget.test')->first() ?? $users->first();
        $jovana = User::where('email', 'jovana@budget.test')->first() ?? $users->get(1);
        $categoryId = Arr::random($categories);

        $exp = Expense::create([
            'paid_by' => $payer->id,
            'category_id' => $categoryId,
            'paid_at' => now()->subDays(3),
            'amount' => 4200.00,
            'description' => 'Namirnice za kuću',
        ]);

        // Marko platio; Jovana i još jedan duguju jednako
        $other = User::query()->whereNotIn('id', [$payer->id, $jovana->id])->inRandomOrder()->first();
        $duznici = collect([$jovana, $other])->filter();

        $per = round($exp->amount / max(1, $duznici->count()), 2);
        $left = $exp->amount - ($per * $duznici->count());

        foreach ($duznici as $i => $u) {
            Split::create([
                'expense_id' => $exp->id,
                'user_id' => $u->id,
                'amount' => $per + ($i === 0 ? $left : 0),
                'settled_at' => null,
            ]);
        }
    }
}
