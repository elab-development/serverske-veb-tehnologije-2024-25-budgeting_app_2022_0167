<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    // GET /api/categories
    public function index(Request $request)
    {
        $q = Category::query()
            ->when($request->filled('search'), fn($qr) =>
                $qr->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name');

        return CategoryResource::collection(
            $q->paginate($request->integer('per_page', 15))->withQueryString()
        );
    }

    // GET /api/categories/{category}
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    // POST /api/categories
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100','unique:categories,name'],
        ]);

        $category = Category::create($data);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    // PUT/PATCH /api/categories/{category}
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required','string','max:100','unique:categories,name,'.$category->id],
        ]);

        $category->update($data);

        return new CategoryResource($category);
    }

    // DELETE /api/categories/{category}
    public function destroy(Category $category)
    {
        $category->delete();
        return response()->noContent();
    }
}
