<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCategory = Category::create([
            'name' => 'uncategorized',
            'is_default' => true,
        ]);

        Category::factory(10)->create();
    }
}
