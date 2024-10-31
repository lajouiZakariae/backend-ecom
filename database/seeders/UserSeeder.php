<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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

        $customers = User::factory()->count(10)->create();

        $customers->each(function (User $customer): void {
            $customer->assignRole(RoleEnum::CUSTOMER);
        });
    }
}
