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
            $affectedRowsCount = Category::destroy($categoryId);

            if ($affectedRowsCount === 0) {
                throw new ResourceNotFoundException(__("Category Not Found"));
            }
    }

    public function deleteMultipleCategories(array $categoryIds, int $userId): void
    {
            $affectedRowsCount = Category::destroy($categoryIds);

            if ($affectedRowsCount === 0) {
                throw new ResourceNotFoundException(__("Category Not Found"));
            }
    }
}
