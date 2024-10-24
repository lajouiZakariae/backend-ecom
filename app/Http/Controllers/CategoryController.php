<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CategoryController
{
    public function __construct(
        private CategoryService $categoryService
    ) {
    }

    public function index(Request $request): ResourceCollection
    {
        $validatedFilters = $request->validate([
            'sortBy' => ['in:oldest,latest'],
            'order' => ['in:asc,desc'],
            'perPage' => ['integer', 'min:1', 'max:100'],
        ]);

        $categories = $this->categoryService->getPaginatedCategories($validatedFilters);

        return CategoryResource::collection($categories);
    }

    public function store(): CategoryResource
    {
        Gate::authorize('create', Category::class);

        $validatedCategoryPayload = request()->validate([
            'image' => ['required', 'image', 'max:2048'],
            'name' => ['required', 'min:1', 'max:255'],
        ]);

        $createdCategory = $this->categoryService->createCategory([
            'user_id' => Auth::id(),
            ...$validatedCategoryPayload,
        ]);

        return CategoryResource::make($createdCategory);
    }

    public function show(int $categoryId): CategoryResource
    {
        return CategoryResource::make($this->categoryService->getCategoryById($categoryId));
    }

    public function update(int $categoryId): CategoryResource
    {
        Gate::authorize('update', Category::class);

        $validatedCategoryPayload = request()->validate([
            'image' => ['nullable', 'image', 'max:2048'],
            'name' => ['required', 'min:1', 'max:255'],
        ]);

        return CategoryResource::make($this->categoryService->updateCategory($categoryId, $validatedCategoryPayload));
    }

    public function destroy(int $categoryId): Response
    {
        Gate::authorize('delete', Category::class);

        $this->categoryService->deleteCatgoryById($categoryId, Auth::id());

        return response()->noContent();
    }

    public function destroyMultiple(Request $request): Response
    {
        Gate::authorize('delete', Category::class);

        $validatedCategoryIds = $request->validate([
            'categoryIds' => ['required', 'array', 'min:1'],
            'categoryIds.*' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $this->categoryService->deleteMultipleCategories($validatedCategoryIds['categoryIds'], Auth::id());

        return response()->noContent();
    }
}
