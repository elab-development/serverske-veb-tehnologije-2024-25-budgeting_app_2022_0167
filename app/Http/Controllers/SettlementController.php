<?php

namespace App\Http\Controllers;

use App\Models\Settlement;
use Illuminate\Http\Request;
use App\Http\Resources\SettlementResource;

class SettlementController extends Controller
{
    // GET /api/settlements?from_user_id=&to_user_id=&per_page=15
    public function index(Request $request)
    {
        $q = Settlement::query()
            ->with(['from','to'])
            ->when($request->filled('from_user_id'), fn($qr) => $qr->where('from_user_id', (int)$request->from_user_id))
            ->when($request->filled('to_user_id'),   fn($qr) => $qr->where('to_user_id', (int)$request->to_user_id))
            ->orderByDesc('paid_at')
            ->orderByDesc('id');

        $perPage = max(1, min(100, (int)$request->query('per_page', 15)));

        return SettlementResource::collection($q->paginate($perPage)->withQueryString());
    }

    // GET /api/settlements/{settlement}
    public function show(Settlement $settlement)
    {
        $settlement->load(['from','to']);
        return new SettlementResource($settlement);
    }

    // POST /api/settlements
    public function store(Request $request)
    {
        $data = $request->validate([
            'from_user_id' => ['required','exists:users,id','different:to_user_id'],
            'to_user_id'   => ['required','exists:users,id'],
            'amount'       => ['required','numeric','min:0.01'],
            'paid_at'      => ['required','date','before_or_equal:now'],
            'note'         => ['nullable','string','max:500'],
        ]);

        $settlement = Settlement::create($data);
        $settlement->load(['from','to']);

        return (new SettlementResource($settlement))
            ->response()
            ->setStatusCode(201);
    }

    // PUT/PATCH /api/settlements/{settlement}
    public function update(Request $request, Settlement $settlement)
    {
        $data = $request->validate([
            'from_user_id' => ['sometimes','exists:users,id','different:to_user_id'],
            'to_user_id'   => ['sometimes','exists:users,id'],
            'amount'       => ['sometimes','numeric','min:0.01'],
            'paid_at'      => ['sometimes','date','before_or_equal:now'],
            'note'         => ['sometimes','nullable','string','max:500'],
        ]);

        $settlement->update($data);
        $settlement->load(['from','to']);

        return new SettlementResource($settlement);
    }

    // DELETE /api/settlements/{settlement}
    public function destroy(Settlement $settlement)
    {
        $settlement->delete();
        return response()->noContent();
    }
}
