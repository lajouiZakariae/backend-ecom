<?php

namespace App\Http\Controllers\Api;

use App\Filters\ProductQueryFilters;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\WhereNumber;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

#[ApiResource('products')]
#[WhereNumber('product')]
class ProductController
{
    public function index(Request $request): ResourceCollection
    {
        $validatedFilters = $request->validate([
            'sortBy' => ['in:oldest,latest'],
            'order' => ['in:asc,desc'],
            'perPage' => ['integer', 'min:1', 'max:100'],
        ]);

        $paginatedProducts = Product::query()
            ->tap(new ProductQueryFilters(
                $request->priceFrom,
                $request->priceTo,
                $request->sortBy,
                $request->order
            ))
            ->paginate($validatedFilters['perPage'] ?? 10);

        return ProductResource::collection($paginatedProducts);
    }

    public function store(ProductStoreRequest $productStoreRequest): ProductResource
    {
        $validatedProductPayload = $productStoreRequest->validated();

        $product = new Product($validatedProductPayload);

        if (!$product->save()) {
            throw new BadRequestHttpException("Product Could not be created");
        }

        return ProductResource::make($product);
    }

    public function show(int $productId): ProductResource
    {
        $product = Product::find($productId);

        if ($product === null) {
            throw new ResourceNotFoundException(__("Product Not Found"));
        }

        return ProductResource::make($product);
    }

    public function update(ProductUpdateRequest $productUpdateRequest, int $productId): ProductResource
    {
        $validatedProductPayload = $productUpdateRequest->validated();

        $affectedRowsCount = Product::where('id', $productId)->update($validatedProductPayload);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__("Product Not Found"));
        }

        return ProductResource::make(Product::find($productId));
    }

    public function destroy(int $productId): Response
    {
        $affectedRowsCount = Product::destroy($productId);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('Product Not Found'));
        }

        return response()->noContent();
    }

    public function destroyMultiple(Request $request): Response
    {
        $validatedProductIds = $request->validate([
            'productIds' => ['required', 'array', 'min:1'],
            'productIds.*' => ['required', 'integer', 'exists:products,id'],
        ]);

        $affectedRowsCount = Product::destroy($validatedProductIds);

        if ($affectedRowsCount === 0) {
            throw new ResourceNotFoundException(__('Product Not Found'));
        }

        return response()->noContent();
    }
}
