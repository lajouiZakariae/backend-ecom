<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use DB;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function getUserCartFeature(): Cart
    {
        return $this->getUserCart();
    }

    public function addProductOrIncrementQuantity(int $productId, int $quantity): CartItem
    {
        $cartItem = $this->getUserCart()->cartItems()->firstOrNew([
            'product_id' => $productId
        ]);

        $cartItem->quantity += $quantity;

        $cartItem->save();

        return $cartItem;
    }

    public function updateProductQuantity(int $productId, int $quantity): Cart
    {
        $affectedRows = DB::table('cart_items')
            ->where('cart_id', $this->getUserCart()->id)
            ->where('product_id', $productId)
            ->update(['quantity' => $quantity]);

        if ($affectedRows === 0) {
            throw new \Exception('Failed to update cart item. Item not found or quantity unchanged.');
        }



        return;
    }

    // Remove a product from the cart
    public function removeProduct(int $productId): Cart
    {
        $this->getUserCart()->cartItems()->where('product_id', $productId)->delete();

        return $this->getUserCart()->refresh();
    }

    // Clear all items from the cart
    public function clearCart(): Cart
    {
        $this->getUserCart()->cartItems()->delete();

        return $this->getUserCart()->refresh();
    }

    // Get the current items in the cart
    public function getCartItems(): array
    {
        return $this->getUserCart()->cartItems()->with('product')->get()->toArray();
    }

    // Calculate the total price of all items in the cart
    public function getCartTotal(): float
    {
        return $this->getUserCart()->cartItems()
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->sum(\DB::raw('cart_items.quantity * products.price'));
    }

    protected function getUserCart(): Cart
    {
        return Cart::firstOrCreate(['user_id' => Auth::id()]);
    }
}
