<?php

namespace App\Services;

use App\Filters\ProductQueryFilters;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use DB;

class ProductService
{
    private $notFoundMessage = "Product Not Found";

    public function getAllProductsMatchFilters(ProductQueryFilters $productQueryFilters): LengthAwarePaginator
    {
        $productsQuery = Product::query()
            ->with(['thumbnail'])
            ->withSum('inventory AS quantity', 'quantity')
            ->tap($productQueryFilters)
            ->paginate(10);

        return $productsQuery;
    }

    public function getProductById(int $productId): Product
    {
        $product = Product::query()
            ->with('thumbnail')
            ->withSum('inventory AS quantity', 'quantity')
            ->find($productId);

        if ($product === null) {
            throw new ResourceNotFoundException(__(':resource Not Found', ['resource' => __('Product')]));
        }

        return $product;
    }

    public function createProduct(array $productPayload): Product
    {
        $product = new Product($productPayload);

        $productSaved = $product->save();

        if (!$productSaved) {
            throw new BadRequestHttpException(__("Product Could not be Created"));
        }

        return $product;
    }

    public function updateProduct(int $productId, array $productPayload): Product
    {
        $affectedRowsCount = Product::where('id', $productId)->update($productPayload);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__(':resource Not Found', ['resource' => __('Product')]));
        }

        return Product::find($productId);
    }

    public function deleteProductById(int $storeId, int $userId): void
    {
        $this->deleteMultipleProductsByIds([$storeId], $userId);
    }

    public function deleteMultipleProducts(array $storeIds, int $userId): void
    {
        $this->deleteMultipleProductsByIds($storeIds, $userId);
    }

    public function deleteMultipleProductsByIds(array $storeIds, int $userId): void
    {
        $affectedRowsCount = Product::where('user_id', $userId)->destroy($storeIds);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('Product Not Found'));
        }
    }
}
