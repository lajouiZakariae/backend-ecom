<?php

namespace App\Http\Controllers\Api;

use App\Enums\RoleEnum;
use App\Filters\ProductQueryFilters;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use DB;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ProductController
{
    public function index(Request $request): ResourceCollection
    {
        $request->validate([
            'sortBy' => ['in:created_at'],
            'order' => ['in:asc,desc'],
            'perPage' => ['integer', 'min:1', 'max:100'],
        ]);

        /**
         * @var \App\Models\User $authUser
         */
        $authUser = auth()->user();

        $paginatedProducts = Product::query()
            ->select('products.*')
            ->when($authUser->hasRole(RoleEnum::CUSTOMER), function (Builder $query) {
                $query
                    ->selectSub(function ($query): void {
                        $query->from('cart_items')
                            ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
                            ->where('carts.user_id', Auth::id())
                            ->select(DB::raw('CAST(SUM(cart_items.quantity) AS SIGNED)'))
                            ->whereColumn('cart_items.product_id', 'products.id')
                            ->groupBy('cart_items.product_id');
                    }, 'cart_quantity')
                    ->selectSub(function ($query): void {
                        $query->from('product_user')
                            ->whereColumn('product_id', 'products.id')
                            ->select(DB::raw('COUNT(product_user.product_id)'))
                            ->where('user_id', Auth::id());
                    }, 'wishlisted_by_authenticated_user');
            })
            ->tap(new ProductQueryFilters(
                $request->priceFrom,
                $request->priceTo,
                $request->sortBy,
                $request->order
            ))
            ->with('category')
            ->paginate($request->perPage ?? 10);

        return ProductResource::collection($paginatedProducts);
    }

    public function store(ProductStoreRequest $productStoreRequest): JsonResponse
    {
        $validatedProductPayload = $productStoreRequest->validated();

        $product = new Product($validatedProductPayload);

        if (!$product->save()) {
            throw new BadRequestHttpException(__("Product Could not be created"));
        }

        return ProductResource::make($product)->response()->setStatusCode(SymfonyResponse::HTTP_CREATED);
    }

    public function show(int $productId): ProductResource
    {
        /**
         * @var Product|null $product
         */
        $product = Product::query()
            ->select('products.*')
            ->selectSub(function ($query): void {
                $query->from('cart_items')
                    ->join('carts', 'cart_items.cart_id', '=', 'carts.id')
                    ->where('carts.user_id', Auth::id())
                    ->select(DB::raw('CAST(SUM(cart_items.quantity) AS SIGNED)'))
                    ->whereColumn('cart_items.product_id', 'products.id')
                    ->groupBy('cart_items.product_id');
            }, 'cart_quantity')
            ->selectSub(function ($query): void {
                $query->from('product_user')
                    ->whereColumn('product_id', 'products.id')
                    ->select(DB::raw('COUNT(product_user.product_id)'))
                    ->where('user_id', Auth::id());
            }, 'wishlisted_by_authenticated_user')
            ->where('products.id', $productId)
            ->with('category')
            ->first();

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
