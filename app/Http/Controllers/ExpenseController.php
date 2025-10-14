<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use App\Http\Resources\ExpenseResource;

class ExpenseController extends Controller
{
    // GET /api/expenses
    public function index(Request $request)
    {
        // ?include=payer,category,splits  (splits automatski učita i splits.user)
        $include = collect(explode(',', (string) $request->query('include', '')))
            ->map(fn($s) => trim($s))
            ->filter()
            ->values();

        $with = [];
        if ($include->contains('payer'))    $with[] = 'payer';
        if ($include->contains('category')) $with[] = 'category';
        if ($include->contains('splits'))   $with[] = 'splits.user';

        $q = Expense::query()
            ->with($with)
            ->when($request->filled('paid_by'),    fn($qr) => $qr->where('paid_by', $request->integer('paid_by')))
            ->when($request->filled('category_id'),fn($qr) => $qr->where('category_id', $request->integer('category_id')))
            ->when($request->filled('date_from'),  fn($qr) => $qr->whereDate('paid_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'),    fn($qr) => $qr->whereDate('paid_at', '<=', $request->date('date_to')))
            ->when($request->filled('min_amount'), fn($qr) => $qr->where('amount', '>=', $request->input('min_amount')))
            ->when($request->filled('max_amount'), fn($qr) => $qr->where('amount', '<=', $request->input('max_amount')))
            ->when($request->filled('search'), function ($qr) use ($request) {
                $s = $request->string('search');
                $qr->where(function ($w) use ($s) {
                    $w->where('description', 'like', "%$s%")
                      ->orWhere('note', 'like', "%$s%");
                });
            })
            ->orderByDesc('paid_at')
            ->orderByDesc('id');

        $perPage = max(1, min(100, $request->integer('per_page', 15)));

        return ExpenseResource::collection(
            $q->paginate($perPage)->withQueryString()
        );
    }

    // GET /api/expenses/{expense}
    public function show(Request $request, Expense $expense)
    {
        $include = collect(explode(',', (string) $request->query('include', '')))
            ->map(fn($s) => trim($s))->filter()->values();

        $with = [];
        if ($include->contains('payer'))    $with[] = 'payer';
        if ($include->contains('category')) $with[] = 'category';
        if ($include->contains('splits'))   $with[] = 'splits.user';

        if ($with) $expense->load($with);

        return new ExpenseResource($expense);
    }

    // POST /api/expenses
    public function store(Request $request)
    {
        $data = $request->validate([
            'paid_by'     => ['required','exists:users,id'],
            'category_id' => ['nullable','exists:categories,id'],
            'paid_at'     => ['required','date','before_or_equal:now'],
            'amount'      => ['required','numeric','min:0.01'],
            'description' => ['nullable','string','max:500'],
            'note'        => ['nullable','string','max:500'],
        ]);

        $expense = Expense::create($data);

        $expense->load(['payer','category']); // praktično za UI posle kreiranja

        return (new ExpenseResource($expense))
            ->response()
            ->setStatusCode(201);
    }

    // PUT/PATCH /api/expenses/{expense}
    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'paid_by'     => ['sometimes','exists:users,id'],
            'category_id' => ['sometimes','nullable','exists:categories,id'],
            'paid_at'     => ['sometimes','date','before_or_equal:now'],
            'amount'      => ['sometimes','numeric','min:0.01'],
            'description' => ['sometimes','nullable','string','max:500'],
            'note'        => ['sometimes','nullable','string','max:500'],
        ]);

        $expense->update($data);

        $expense->load(['payer','category']);

        return new ExpenseResource($expense);
    }

    // DELETE /api/expenses/{expense}
    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->noContent();
    }
}
