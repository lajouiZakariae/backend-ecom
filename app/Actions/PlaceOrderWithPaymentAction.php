<?php

namespace App\Actions;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlaceOrderWithPaymentAction
{
    public function handle(): void
    {
        $cart = Cart::where('user_id', Auth::id())->with('cartItems.product')->first();

        if (!$cart) {
            throw new NotFoundHttpException(__(':reource not found', ['resource' => __('Cart')]));
        }

        if ($cart->cartItems->isEmpty()) {
            throw new BadRequestHttpException(__('Cart cannot be empty'));
        }

        $order = DB::transaction(function () use ($cart): Order {
            $order = new Order([
                'user_id' => Auth::id(),
            ]);

            $cart->cartItems->each(function (CartItem $cartItem) use ($order): void {
                $order->orderItems()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'product_sold_price' => $cartItem->product->price,
                    'product_title' => $cartItem->product->title,
                ]);
            });

            return $order;
        });

        if (!$order->save()) {
            throw new BadRequestHttpException(__("Order Could not be created"));
        }
    }
}
