<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $statuses = [
            'Imported',
            'QC Done',
            'Packed/Shipped',
            'Returned',
            'Invoiced',
            'Cancelled',
            'Pending',
            'Picked',
            'Returned to Warehouse',
            'For Claims'
        ];

        $statuses = [
           ['name' => 'Imported'],
           ['name' => 'QC Done'],
           ['name' => 'Packed/Shipped'],
           ['name' => 'Returned'],
           ['name' => 'Invoiced'],
           ['name' => 'Cancelled'],
           ['name' => 'Pending'],
           ['name' => 'Picked'],
           ['name' => 'Returned to Warehouse'],
           ['name' => 'For Claims']
        ];

         DB::table('order_statuses')->insert($statuses);
    }
}
