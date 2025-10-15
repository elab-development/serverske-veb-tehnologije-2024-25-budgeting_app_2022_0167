<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Expense;
use App\Models\Split;
use App\Models\Settlement;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserExportController extends Controller
{
public function exportUserTransactions(Request $request, User $user): StreamedResponse
    {
        $auth = $request->user();
        if (!$auth || (!$auth->isAdmin() && $auth->id !== $user->id)) {
            abort(403, 'Forbidden');
        }

        $df = $request->date('date_from');
        $dt = $request->date('date_to');
        $filename = sprintf('transactions_user_%d_%s.csv', $user->id, now()->format('Ymd_His'));

        return response()->stream(function () use ($user, $df, $dt) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Type','Date','Amount','Direction','Counterparty']);

            // Expenses (user paid → out)
            Expense::where('paid_by', $user->id)
                ->when($df, fn($q) => $q->whereDate('paid_at', '>=', $df))
                ->when($dt, fn($q) => $q->whereDate('paid_at', '<=', $dt))
                ->get(['paid_at','amount'])
                ->each(function ($e) use ($out) {
                    fputcsv($out, ['Expense',
                        optional($e->paid_at)->format('Y-m-d'),
                        number_format((float)$e->amount, 2, '.', ''),
                        'out',
                        ''
                    ]);
                });

            // Splits (owes/paid)
            Split::with(['expense:id,paid_at,paid_by','expense.payer:id,name'])
                ->where('user_id', $user->id)
                ->when($df, fn($q) => $q->whereHas('expense', fn($qq) => $qq->whereDate('paid_at','>=',$df)))
                ->when($dt, fn($q) => $q->whereHas('expense', fn($qq) => $qq->whereDate('paid_at','<=',$dt)))
                ->get(['id','expense_id','user_id','amount','settled_at'])
                ->each(function ($s) use ($out) {
                    $dir  = $s->settled_at ? 'paid' : 'owes';
                    $date = $s->settled_at ? optional($s->settled_at)->format('Y-m-d')
                                        : optional($s->expense?->paid_at)->format('Y-m-d');
                    $cp   = optional($s->expense?->payer)->name ?? '';
                    fputcsv($out, ['Split',
                        $date,
                        number_format((float)$s->amount, 2, '.', ''),
                        $dir,
                        $cp
                    ]);
                });

            // Settlements (out/in)
            Settlement::with(['from:id,name','to:id,name'])
                ->where(fn($q) => $q->where('from_user_id',$user->id)->orWhere('to_user_id',$user->id))
                ->when($df, fn($q) => $q->whereDate('paid_at','>=',$df))
                ->when($dt, fn($q) => $q->whereDate('paid_at','<=',$dt))
                ->get(['from_user_id','to_user_id','paid_at','amount'])
                ->each(function ($st) use ($out, $user) {
                    $dir = $st->from_user_id === $user->id ? 'out' : 'in';
                    $cp  = $st->from_user_id === $user->id ? ($st->to->name ?? '')
                                                        : ($st->from->name ?? '');
                    fputcsv($out, ['Settlement',
                        optional($st->paid_at)->format('Y-m-d'),
                        number_format((float)$st->amount, 2, '.', ''),
                        $dir,
                        $cp
                    ]);
                });

            fclose($out);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
        ]);
    }

}
