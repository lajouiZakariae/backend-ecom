<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use \Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class CartController
{
    public function show(): CartResource
    {
        $cart = $this->getUserCart();

        return new CartResource($cart);
    }

    public function addProductOrIncrementQuantity(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer']
        ]);

        $cartItem = $this->getUserCart()->cartItems()->firstOrNew([
            'product_id' => $request->productId
        ]);

        $cartItem->quantity += $request->integer('quantity', 1);

        if (!$cartItem->save()) {
            throw new BadRequestHttpException(__('Could not increment quantity'));
        }

        return CartItemResource::make($cartItem)
            ->response()
            ->setStatusCode($cartItem->wasRecentlyCreated ? SymfonyResponse::HTTP_CREATED : SymfonyResponse::HTTP_OK);
    }

    public function update(Request $request, int $productId): CartItemResource
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:1']
        ]);

        $cartItem = $this->getUserCart()->cartItems()->where('product_id', $productId)->first();

        if (!$cartItem) {
            throw new BadRequestHttpException(__('Product not found in cart'));
        }

        $cartItem->quantity = $request->input('quantity');

        if (!$cartItem->save()) {
            throw new BadRequestHttpException(__('Could not update quantity'));
        }

        return new CartItemResource($cartItem);
    }

    public function removeProduct(int $productId): JsonResponse
    {
        $this->getUserCart()->cartItems()->where('product_id', $productId)->delete();

        return response()->noContent();
    }

    public function decrementQuantityOrDeleteProduct(Request $request, int $productId): CartItemResource|JsonResponse
    {
        $cartItem = $this->getUserCart()->cartItems()->where('product_id', $productId)->first();

        if (!$cartItem) {
            throw new BadRequestHttpException(__('Product not found in cart'));
        }

        if ($cartItem->quantity > 1) {
            $cartItem->quantity--;

            $cartItem->save();

            return new CartItemResource($cartItem);
        }

        $cartItem->delete();

        return response()->noContent();
    }

    public function clearCart(): JsonResponse
    {
        $this->getUserCart()->cartItems()->delete();

        return response()->noContent();
    }

    protected function getUserCart(): Cart
    {
        return Cart::where('user_id', Auth::id())->firstOrCreate();
    }
}
