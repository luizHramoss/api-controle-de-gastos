<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = $request->user()
            ->categories()
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Categories retrieved successfully.',
            'data'    => CategoryResource::collection($categories),
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $request->user()->categories()->create([
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'Category created successfully.',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    public function show(Request $request, Category $category): JsonResponse
    {
        $this->authorizeCategory($request, $category);

        return response()->json([
            'message' => 'Category retrieved successfully.',
            'data'    => new CategoryResource($category),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorizeCategory($request, $category);

        $category->update(['name' => $request->name]);

        return response()->json([
            'message' => 'Category updated successfully.',
            'data'    => new CategoryResource($category),
        ]);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        $this->authorizeCategory($request, $category);

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
            'data'    => null,
        ]);
    }

    private function authorizeCategory(Request $request, Category $category): void
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to access this category.');
        }
    }
}
