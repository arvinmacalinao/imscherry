<?php

namespace Database\Seeders;

use App\Models\ShopName;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ShopNameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'SHOPEE LUXELLE', 'platform_id' => 1],
            ['name' => 'LAZADA LUXELLE', 'platform_id' => 2],
            ['name' => 'TIKTOK LUXELLE', 'platform_id' => 3],
            ['name' => 'SHOPIFY LUXELLE', 'platform_id' => 4],
            ['name' => 'HEAD OFFICE', 'platform_id' => 6],
            ['name' => 'SMOK', 'platform_id' => 6],
            ['name' => 'VIBER COMMUNITY', 'platform_id' => 6],
            ['name' => 'CHERRY SHOP', 'platform_id' => 4],
            ['name' => 'SHOPEE CHERRY MOBILE', 'platform_id' => 1],
            ['name' => 'SHOPEE CHERRY', 'platform_id' => 1],
            ['name' => 'LAZADA CHERRY', 'platform_id' => 2],
            ['name' => 'TIKTOK CHERRY', 'platform_id' => 3],
            ['name' => 'EDAMAMA CHERRY', 'platform_id' => 6],
        ];

        foreach ($data as $item) {
            ShopName::create($item);
        }
    }
}
