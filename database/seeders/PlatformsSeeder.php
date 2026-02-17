<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PlatformsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platforms = [
            ['name' => 'Shopee'],
            ['name' => 'Lazada'],
            ['name' => 'TikTok'],
            ['name' => 'Shopify'],
            ['name' => 'Zalora'],
            ['name' => 'Others'],
        ];

        DB::table('platforms')->insert($platforms);
    }
}
