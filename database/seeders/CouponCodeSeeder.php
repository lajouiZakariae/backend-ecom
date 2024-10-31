<?php

namespace Database\Seeders;

use App\Models\CouponCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CouponCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CouponCode::factory(10)->create();
    }
}
