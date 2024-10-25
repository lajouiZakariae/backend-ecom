<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WishlistedProductController extends Controller
{
    public function index(): ResourceCollection
    {
        $wishlistedProducts = Auth::user()->wishlistedProducts;

        return ProductResource::collection($wishlistedProducts);
    }

    public function store(int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new NotFoundHttpException(__('Product not found'));
        }

        Auth::user()->wishlistedProducts()->attach($productId);

        return ProductResource::make($product)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(int $productId): JsonResponse
    {
        $product = Product::find($productId);

        if (!$product) {
            throw new NotFoundHttpException(__('Product not found'));
        }

        Auth::user()->wishlistedProducts()->detach($productId);

        return response()->noContent();
    }
}
