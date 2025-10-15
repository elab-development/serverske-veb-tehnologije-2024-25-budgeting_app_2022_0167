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
        $ttl      = now()->addMinutes(60);

        $data = Cache::remember($cacheKey, $ttl, function () use ($from, $to) {
            // 1) Frankfurter (ECB) — ispravan domen .app
            try {
                $res = Http::timeout(8)->get('https://api.frankfurter.app/latest', [
                    'from' => $from,
                    'to'   => $to,
                ]);

                if ($res->ok()) {
                    $j = $res->json();
                    return [
                        'provider' => 'frankfurter',
                        'date'  => $j['date'] ?? null,
                        'base'  => $j['base'] ?? $from,
                        'rates' => $j['rates'] ?? [],
                    ];
                }
            } catch (\Throwable $e) {
                // ignore, probamo fallback
            }

            // 2) Fallback: exchangerate.host (free, no key)
            $res2 = Http::timeout(8)->get('https://api.exchangerate.host/latest', [
                'base'     => $from,
                'symbols'  => $to,
            ])->throw()->json();

            return [
                'provider' => 'exchangerate.host',
                'date'  => $res2['date'] ?? null,
                'base'  => $res2['base'] ?? $from,
                'rates' => $res2['rates'] ?? [],
            ];
        });

        return response()->json($data);
    }
}
