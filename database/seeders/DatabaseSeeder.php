<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            CouponCodeSeeder::class,
            ProductSeeder::class,
        ]);

        $customer = User::where('email', 'customer@one.com')->first();

        $cart = Cart::firstOrCreate(['user_id' => $customer->id]);

        $products = Product::take(2)->get();

        foreach ($products as $index => $product) {
            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_id' => $product->id,
                ],
                [
                    'quantity' => 2 + $index
                ]
            );
        }

        $customer->wishlistedProducts()->attach($products[0]->id);
    }
}
