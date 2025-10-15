<?php

namespace App\Http\Controllers;

use App\Models\Split;
use Illuminate\Http\Request;
use App\Http\Resources\SplitResource;
use App\Mail\SplitAssignedMail;
use App\Mail\SplitSettledMail;
use Illuminate\Support\Facades\Mail;

class SplitController extends Controller
{
    // GET /api/splits
    public function index(Request $request)
    {
        $q = Split::query()
            ->with(['user','expense'])
            ->when($request->filled('expense_id'), fn($qr) => $qr->where('expense_id', $request->integer('expense_id')))
            ->when($request->filled('user_id'),    fn($qr) => $qr->where('user_id', $request->integer('user_id')))
            ->when($request->filled('settled'),    function ($qr) use ($request) {
                $settled = (int) $request->query('settled'); // 1 ili 0
                $settled ? $qr->whereNotNull('settled_at') : $qr->whereNull('settled_at');
            })
            ->orderByDesc('id');

        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        return SplitResource::collection($q->paginate($perPage)->withQueryString());
    }

    // GET /api/splits/{split}
    public function show(Request $request, Split $split)
    {
        $include = collect(explode(',', (string) $request->query('include', '')))
            ->map(fn($s) => trim($s))->filter()->values();

        $with = [];
        if ($include->contains('user'))    $with[] = 'user';
        if ($include->contains('expense')) $with[] = 'expense';
        if ($with) $split->load($with);

        return new SplitResource($split);
    }

    // POST /api/splits
    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_id' => ['required','exists:expenses,id'],
            'user_id'    => ['required','exists:users,id'],
            'amount'     => ['required','numeric','min:0.01'],
            'settled_at' => ['nullable','date'],
        ]);
 

        $split = Split::create($data);
        $split->load(['user','expense.payer','expense.category']);

        //   mejl učesniku kome je dodeljeno učešće
        Mail::to($split->user->email)->send(new SplitAssignedMail($split));


        return (new SplitResource($split))
            ->response()
            ->setStatusCode(201);
    }

    // PUT/PATCH /api/splits/{split}
    public function update(Request $request, Split $split)
    {
        $data = $request->validate([
            'expense_id' => ['sometimes','exists:expenses,id'],
            'user_id'    => ['sometimes','exists:users,id'],
            'amount'     => ['sometimes','numeric','min:0.01'],
            'settled_at' => ['sometimes','nullable','date'],
        ]);

        $split->update($data);
        $split->load(['user','expense']);

        return new SplitResource($split);
    }

    // POST /api/splits/{split}/settle
    public function settle(Request $request, Split $split)
    {
        $data = $request->validate([
            'settled_at' => ['nullable','date'],
        ]);

        $split->settled_at = $data['settled_at'] ?? now();
        $split->save();

        $split->load(['user','expense.payer']);
        //   obavesti i platioca i učesnika
        Mail::to($split->user->email)->send(new SplitSettledMail($split, true));
        Mail::to($split->expense->payer->email)->send(new SplitSettledMail($split, true));

        return new SplitResource($split->fresh(['user','expense']));
    }

    // POST /api/splits/{split}/unsettle
    public function unsettle(Request $request, Split $split)
    {
        $split->settled_at = null;
        $split->save();

        return new SplitResource($split->fresh(['user','expense']));
    }

    // DELETE /api/splits/{split}
    public function destroy(Split $split)
    {
        $split->delete();
        return response()->noContent();
    }
}
