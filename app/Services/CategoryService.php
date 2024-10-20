<?php

namespace App\Services;

use App\Filters\CategoryFilters;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use DB;

class CategoryService
{
    public function getPaginatedCategories(array $filters): LengthAwarePaginator
    {
        $paginatedCategories = Category::query()
            ->tap(new CategoryFilters($filters['sortBy'], $filters['order']))
            ->paginate($filters['perPage'] ?? 10);

        return $paginatedCategories;
    }

    public function getCategoryById(int $categoryId): Category
    {
        $category = Category::find($categoryId);

        if ($category === null) {
            throw new ResourceNotFoundException(__("Category Not Found"));
        }

        return $category;
    }

    public function createCategory(array $categoryPayload): Category
    {
        $category = new Category($categoryPayload);

        if (!$category->save()) {
            throw new BadRequestHttpException("Category Could not be created");
        }

        return $category;
    }

    public function updateCategory(int $categoryId, array $categoryPayload): Category
    {
        $affectedRowsCount = Category::where('id', $categoryId)->update($categoryPayload);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__("Category Not Found"));
        }

        return Category::find($categoryId);
    }

    public function deleteCatgoryById(int $categoryId, int $userId): void
    {
        $defaultCategory = Category::where('user_id', $userId)
            ->where('is_default', true)
            ->first();

        if ($defaultCategory === null) {
            throw new BadRequestHttpException("Default Category Not Found");
        }

        if ($categoryId === $defaultCategory->id) {
            throw new BadRequestHttpException("Category cannot be deleted");
        }

        $defaultCategoryId = $defaultCategory->id;

        DB::transaction(function () use ($defaultCategoryId, $categoryId, $userId): void {
            Product::where('user_id', $userId)
                ->where('category_id', $categoryId)
                ->update(['category_id' => $defaultCategoryId]);

            $affectedRowsCount = Category::where('user_id', $userId)->destroy($categoryId);

            if ($affectedRowsCount === 0) {
                throw new ResourceNotFoundException(__("Category Not Found"));
            }
        });
    }

    public function deleteMultipleCategories(array $categoryIds, int $userId): void
    {
        $defaultCategory = Category::where('user_id', $userId)
            ->where('is_default', true)
            ->first();

        if ($defaultCategory === null) {
            throw new BadRequestHttpException("Default Category Not Found");
        }

        if (in_array($defaultCategory->id, $categoryIds)) {
            throw new BadRequestHttpException("Category cannot be deleted");
        }

        DB::transaction(function () use ($defaultCategory, $categoryIds, $userId): void {
            $defaultCategoryId = $defaultCategory->id;

            Product::where('user_id', $userId)
                ->whereIn('category_id', $categoryIds)
                ->update(['category_id' => $defaultCategoryId]);

            $affectedRowsCount = Category::where('user_id', $userId)->destroy($categoryIds);

            if ($affectedRowsCount === 0) {
                throw new ResourceNotFoundException(__("Category Not Found"));
            }
        });
    }
}
