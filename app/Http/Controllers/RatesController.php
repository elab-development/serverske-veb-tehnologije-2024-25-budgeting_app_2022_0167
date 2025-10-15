<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RatesController extends Controller
{
    public function latest(Request $req)
    {
        $from = strtoupper($req->query('from', 'EUR'));
        $to   = strtoupper($req->query('to',   'USD,RSD'));

        $cacheKey = "fx:$from:$to";
        $data = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($from, $to) {
            $res = Http::timeout(8)->get('https://api.frankfurter.dev/latest', [
                'from' => $from,
                'to'   => $to,
            ])->throw()->json();

            return [
                'date'  => $res['date'] ?? null,
                'base'  => $res['base'] ?? $from,
                'rates' => $res['rates'] ?? [],
            ];
        });

        return response()->json($data);
    }
}
