<?php

namespace App\Http\Controllers\Api;

use App\Filters\CategoryFilters;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\Product;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\WhereNumber;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

#[ApiResource('categories')]
#[WhereNumber('category')]
class CategoryController
{
    public function __construct(
        private MediaStorageService $mediaStorageService
    ) {
    }

    public function index(Request $request): ResourceCollection
    {
        $validatedFilters = $request->validate([
            'sortBy' => ['in:name,created_at'],
            'order' => ['in:asc,desc'],
            'perPage' => ['integer', 'min:1', 'max:100'],
        ]);

        $paginatedCategories = Category::query()
            ->tap(new CategoryFilters($validatedFilters['sortBy'], $validatedFilters['order']))
            ->paginate($validatedFilters['perPage'] ?? 10);

        return CategoryResource::collection($paginatedCategories);
    }

    public function store(Request $request): CategoryResource
    {
        $validatedCategoryPayload = $request->validate([
            'image' => ['required', 'image', 'max:2048'],
            'name' => ['required', 'min:1', 'max:255'],
        ]);

        $category = new Category($validatedCategoryPayload);

        if ($request->hasFile('image')) {
            $this->mediaStorageService->storeImageAndAssignToModel($category, 'image', 'image');
        }

        if (!$category->save()) {
            throw new BadRequestHttpException("Category Could not be created");
        }

        return CategoryResource::make($category);
    }

    public function show(int $categoryId): CategoryResource
    {
        $category = Category::find($categoryId);

        if ($category === null) {
            throw new ResourceNotFoundException(__("Category Not Found"));
        }

        return CategoryResource::make($category);
    }

    public function update(Request $request, int $categoryId): CategoryResource
    {
        $validatedCategoryPayload = $request->validate([
            'image' => ['nullable', 'image', 'max:2048'],
            'name' => ['required', 'min:1', 'max:255'],
        ]);

        $affectedRowsCount = Category::where('id', $categoryId)->update($validatedCategoryPayload);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__("Category Not Found"));
        }

        $category = Category::find($categoryId);

        if ($request->hasFile('image')) {
            $this->mediaStorageService->clearMediaOfModel($category, 'image');
            $this->mediaStorageService->storeImageAndAssignToModel($category, 'image', 'image');
        }

        return CategoryResource::make($category);
    }

    public function destroy(int $categoryId): Response
    {
        $defaultCategory = Category::where('is_default', true)->first();

        if ($defaultCategory === null) {
            throw new BadRequestHttpException("Default Category Not Found");
        }

        if ($categoryId === $defaultCategory->id) {
            throw new BadRequestHttpException("Category cannot be deleted");
        }

        $defaultCategoryId = $defaultCategory->id;

        DB::transaction(function () use ($defaultCategoryId, $categoryId): void {
            Product::where('category_id', $categoryId)->update(['category_id' => $defaultCategoryId]);

            $affectedRowsCount = Category::destroy($categoryId);

            if ($affectedRowsCount === 0) {
                throw new ResourceNotFoundException(__("Category Not Found"));
            }
        });

        return response()->noContent();
    }

    public function destroyMultiple(Request $request): Response
    {
        $validatedCategoryIds = $request->validate([
            'categoryIds' => ['required', 'array', 'min:1'],
            'categoryIds.*' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $defaultCategory = Category::where('is_default', true)->first();

        if ($defaultCategory === null) {
            throw new BadRequestHttpException("Default Category Not Found");
        }

        if (in_array($defaultCategory->id, $validatedCategoryIds)) {
            throw new BadRequestHttpException("Category cannot be deleted");
        }

        DB::transaction(function () use ($defaultCategory, $validatedCategoryIds): void {
            $defaultCategoryId = $defaultCategory->id;

            Product::whereIn('category_id', $validatedCategoryIds)->update(['category_id' => $defaultCategoryId]);

            $affectedRowsCount = Category::destroy($validatedCategoryIds);

            if ($affectedRowsCount === 0) {
                throw new ResourceNotFoundException(__("Category Not Found"));
            }
        });

        return response()->noContent();
    }
}
