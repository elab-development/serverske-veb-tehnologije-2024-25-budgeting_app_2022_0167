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

        // Header: minimalan skup kolona
        fputcsv($out, ['Type','Date','Amount','Direction','Counterparty','Note']);

        // Expenses koje je korisnik platio (out)
        $expenses = Expense::where('paid_by', $user->id)
            ->when($df, fn($q) => $q->whereDate('paid_at', '>=', $df))
            ->when($dt, fn($q) => $q->whereDate('paid_at', '<=', $dt))
            ->get(['paid_at','amount','note']);

        foreach ($expenses as $e) {
            fputcsv($out, ['Expense', optional($e->paid_at)->format('Y-m-d'),
                number_format((float)$e->amount, 2, '.', ''), 'out', '', $e->note]);
        }

        // Splits gde korisnik duguje/je platio (owes/paid)
        $splits = Split::with('expense.payer')
            ->where('user_id', $user->id)
            ->when($df, fn($q) => $q->whereHas('expense', fn($qq) => $qq->whereDate('paid_at','>=',$df)))
            ->when($dt, fn($q) => $q->whereHas('expense', fn($qq) => $qq->whereDate('paid_at','<=',$dt)))
            ->get();

        foreach ($splits as $s) {
            $dir  = $s->settled_at ? 'paid' : 'owes';
            $date = $s->settled_at ? optional($s->settled_at)->format('Y-m-d')
                                   : optional($s->expense?->paid_at)->format('Y-m-d');
            fputcsv($out, ['Split', $date,
                number_format((float)$s->amount, 2, '.', ''), $dir,
                optional($s->expense?->payer)->name, '']);
        }

        // Settlements koje je slao/primio (out/in)
        $settlements = Settlement::with(['from','to'])
            ->where(fn($q) => $q->where('from_user_id',$user->id)->orWhere('to_user_id',$user->id))
            ->when($df, fn($q) => $q->whereDate('paid_at','>=',$df))
            ->when($dt, fn($q) => $q->whereDate('paid_at','<=',$dt))
            ->get(['from_user_id','to_user_id','paid_at','amount','note']);

        foreach ($settlements as $st) {
            $dir = $st->from_user_id === $user->id ? 'out' : 'in';
            $cp  = $st->from_user_id === $user->id ? ($st->to->name ?? '—') : ($st->from->name ?? '—');
            fputcsv($out, ['Settlement', optional($st->paid_at)->format('Y-m-d'),
                number_format((float)$st->amount, 2, '.', ''), $dir, $cp, $st->note]);
        }

        fclose($out);
    }, 200, [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        'Cache-Control'       => 'no-store, no-cache, must-revalidate',
    ]);
}

}
