<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTransactionTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['name' => 'transfer_in'],
            ['name' => 'transfer_out'],
            ['name' => 'PO'],
            ['name' => 'audit in'],
            ['name' => 'audit out'],
        ];

        DB::table('product_transaction_types')->insert($types);
    }
}
