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

    //  neto dugovanja (debtor -> creditor) iz splits + settlements
    // GET /api/admin/stats/debts-matrix?date_from=...&date_to=...&min_net=0.01
    public function debtsMatrix(Request $request)
    {
        $from = $request->date('date_from')?->format('Y-m-d') ?? now()->subDays(90)->toDateString();
        $to   = $request->date('date_to')?->format('Y-m-d')   ?? now()->toDateString();
        $min  = (float) $request->query('min_net', 0.01);

        $sql = "
            WITH debts AS (
                SELECT s.user_id AS debtor_id, e.paid_by AS creditor_id, SUM(s.amount) AS owed
                FROM splits s
                JOIN expenses e ON e.id = s.expense_id
                WHERE DATE(e.paid_at) BETWEEN :from AND :to
                GROUP BY s.user_id, e.paid_by
            ),
            sett AS (
                SELECT from_user_id AS debtor_id, to_user_id AS creditor_id, SUM(amount) AS paid
                FROM settlements
                WHERE DATE(paid_at) BETWEEN :from AND :to
                GROUP BY from_user_id, to_user_id
            ),
            merged AS (
                SELECT d.debtor_id, d.creditor_id, COALESCE(d.owed,0) - COALESCE(s.paid,0) AS net
                FROM debts d LEFT JOIN sett s
                  ON s.debtor_id=d.debtor_id AND s.creditor_id=d.creditor_id
                UNION ALL
                SELECT s.debtor_id, s.creditor_id, 0 - COALESCE(s.paid,0) AS net
                FROM sett s LEFT JOIN debts d
                  ON d.debtor_id=s.debtor_id AND d.creditor_id=s.creditor_id
                WHERE d.debtor_id IS NULL
            )
            SELECT m.debtor_id, du.name AS debtor_name,
                   m.creditor_id, cu.name AS creditor_name,
                   ROUND(SUM(m.net),2) AS net_amount
            FROM merged m
            JOIN users du ON du.id=m.debtor_id
            JOIN users cu ON cu.id=m.creditor_id
            GROUP BY m.debtor_id, du.name, m.creditor_id, cu.name
            HAVING ROUND(SUM(m.net),2) >= :min
            ORDER BY net_amount DESC, debtor_name, creditor_name
        ";

        $rows = DB::select($sql, ['from'=>$from, 'to'=>$to, 'min'=>$min]);

        return response()->json([
            'period' => ['from'=>$from, 'to'=>$to],
            'data'   => $rows,
        ]);
    }
}
