<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CartItemResource;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Post;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CartController
{
    #[Get('cart')]
    public function show(): CartResource
    {
        $cart = $this->getUserCart();

        return new CartResource($cart);
    }

    #[Post('cart/products')]
    public function addProductOrIncrementQuantity(Request $request): CartItemResource
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

        return new CartItemResource($cartItem);
    }

    #[Patch('cart/products/{productId}')]
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

    #[Delete('cart/product/{productId}')]
    public function removeProduct(int $productId): JsonResponse
    {
        $this->getUserCart()->cartItems()->where('product_id', $productId)->delete();

        return response()->noContent();
    }

    #[Patch('cart/product/{productId}/decrement')]
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

    #[Delete('cart/clear')]
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
