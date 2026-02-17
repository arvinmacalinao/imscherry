<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin',               'slug' => 'admin'],
            ['name' => 'Accounting',          'slug' => 'accounting'],
            ['name' => 'E-commerce Assistant','slug' => 'ecommerce-assistant'],
            ['name' => 'Warehouse',           'slug' => 'warehouse'],
            ['name' => 'Quality Control',     'slug' => 'qc'],
            ['name' => 'Packer',              'slug' => 'packer'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['slug' => $role['slug']], 
                [
                    'name'       => $role['name'],
                    'slug'       => $role['slug'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
