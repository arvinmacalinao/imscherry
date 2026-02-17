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
        $categories = collect([
            [
                'id'    => 1,
                'name'  => 'Cherry Products',
                'slug'  => 'cherry-products',
                'created_at' => now()
            ],
            [
                'id'    => 2,
                'name'  => 'Luxelle Products',
                'slug'  => 'luxelle',
                'created_at' => now()
            ],
        ]);

        $categories->each(function ($category){
            Category::insert($category);
        });
    }
}
