<?php

namespace App\Http\Controllers;

use App\Filters\ProductQueryFilters;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService)
    {
    }

    public function index(Request $request): ResourceCollection
    {
        $request->validate([
            'priceFrom' => ['nullable', 'numeric', 'min:0'],
            'priceTo' => ['nullable', 'numeric', 'min:0'],
            'costFrom' => ['nullable', 'numeric', 'min:0'],
            'costTo' => ['nullable', 'numeric', 'min:0'],
            'sortBy' => ['nullable', 'in:price,cost,created_at'],
            'order' => ['nullable', 'in:asc,desc'],
            'perPage' => ['integer', 'min:1', 'max:100'],
        ]);

        $productFilters = new ProductQueryFilters(
            $request->priceFrom,
            $request->priceTo,
            $request->sortBy,
            $request->order
        );

        $paginatedProducts = $this->productService->getAllProductsMatchFilters($productFilters);

        return ProductResource::collection($paginatedProducts);
    }

    public function store(ProductStoreRequest $productStoreRequest): ProductResource
    {
        $validatedProductPayload = $productStoreRequest->validated();

        $createdProduct = $this->productService->createProduct([
            'user_id' => Auth::id(),
            ...$validatedProductPayload,
        ]);

        return ProductResource::make($createdProduct);
    }

    public function show(int $productId): ProductResource
    {
        return ProductResource::make($this->productService->getProductById($productId));
    }

    public function update(ProductUpdateRequest $productUpdateRequest, int $productId): ProductResource
    {
        $validatedProductPayload = $productUpdateRequest->validated();

        return ProductResource::make($this->productService->updateProduct($productId, $validatedProductPayload));
    }

    public function destroy(int $productId): Response
    {
        $this->productService->deleteProductById($productId, Auth::id());

        return response()->noContent();
    }

    public function destroyMultiple(Request $request): Response
    {
        $validatedProductIds = $request->validate([
            'productIds' => ['required', 'array', 'min:1'],
            'productIds.*' => ['required', 'integer', 'exists:products,id'],
        ]);

        $this->productService->deleteMultipleProducts($validatedProductIds['productIds'], Auth::id());

        return response()->noContent();
    }
}
