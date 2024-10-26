<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        RoleEnum::map(fn(string $name): Role => Role::create(['name' => $name]));

        $user = User::factory()->create([
            'first_name' => 'Customer',
            'last_name' => 'One',
            'email' => 'customer@one.com',
        ]);

        $user->assignRole(RoleEnum::CUSTOMER);

        $admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'One',
            'email' => 'admin@one.com',
        ]);

        $admin->assignRole(RoleEnum::ADMIN);

        Category::factory(10)->create();

        Product::factory(5)->create();

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

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

        $user->wishlistedProducts()->attach($products[0]->id);
    }
}
