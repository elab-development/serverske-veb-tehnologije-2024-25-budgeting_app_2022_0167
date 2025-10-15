<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminStatsController extends Controller
{
    //   sumarni pregled rashoda u periodu
    // GET /api/admin/stats/overview?date_from=2025-01-01&date_to=2025-12-31
    public function overview(Request $request)
    {
        $from = $request->date('date_from')?->format('Y-m-d') ?? now()->subDays(90)->toDateString();
        $to   = $request->date('date_to')?->format('Y-m-d')   ?? now()->toDateString();

        $row = DB::table('expenses')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$from, $to])
            ->selectRaw('COUNT(*) AS cnt, COALESCE(SUM(amount),0) AS total, COALESCE(AVG(amount),0) AS avg_amount')
            ->first();

        return response()->json([
            'period' => ['from' => $from, 'to' => $to],
            'count'  => (int)($row->cnt ?? 0),
            'total'  => (float)($row->total ?? 0),
            'avg'    => (float)($row->avg_amount ?? 0),
        ]);
    }

   
}
