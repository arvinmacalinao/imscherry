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
        $categories = [
        ['id' => 1,  'name' => 'CHERRY FREEBIES',           'slug' => 'cherry-freebies'],
        ['id' => 2,  'name' => 'CHERRY MT',                 'slug' => 'cherry-mt'],
        ['id' => 3,  'name' => 'CHERRY IOT',                'slug' => 'cherry-iot'],
        ['id' => 4,  'name' => 'CHERRY NON IOT',            'slug' => 'cherry-non-iot'],
        ['id' => 5,  'name' => 'CHERRY Replacement Filters','slug' => 'cherry-replacement-filters'],
        ['id' => 6,  'name' => 'CHERRY Accessories',        'slug' => 'cherry-accessories'],
        ['id' => 7,  'name' => 'CHERRY X SANRIO',           'slug' => 'cherry-x-sanrio'],
        ['id' => 8,  'name' => 'CHERRY Roam',               'slug' => 'cherry-roam'],
        ['id' => 9,  'name' => 'AVITA',                     'slug' => 'avita'],
        ['id' => 10, 'name' => 'LUXELLE Skin Care',         'slug' => 'luxelle-skin-care'],
        ['id' => 11, 'name' => 'LUXELLE Devices',           'slug' => 'luxelle-devices'],
        ['id' => 12, 'name' => 'LUXELLE Sets',              'slug' => 'luxelle-sets'],
        ['id' => 13, 'name' => 'VITASENSE',                 'slug' => 'vitasense'],
        ['id' => 14, 'name' => 'ULTIMA',                    'slug' => 'ultima'],
        ['id' => 15, 'name' => 'SMOK Disposable',           'slug' => 'smok-disposable'],
        ['id' => 16, 'name' => 'SMOK Device',               'slug' => 'smok-device'],
        ['id' => 17, 'name' => 'SMOK Pod',                  'slug' => 'smok-pod'],
        ['id' => 18, 'name' => 'RELX Disposable',           'slug' => 'relx-disposable'],
        ['id' => 19, 'name' => 'RELX Device',               'slug' => 'relx-device'],
        ['id' => 20, 'name' => 'RELX Pod',                  'slug' => 'relx-pod'],
        ['id' => 21, 'name' => 'ONEBAR Device',             'slug' => 'onebar-device'],
        ['id' => 22, 'name' => 'ONEBAR Pod',                'slug' => 'onebar-pod'],
        ['id' => 23, 'name' => 'XVAPE Device',              'slug' => 'xvape-device'],
        ['id' => 24, 'name' => 'XVAPE Pod',                 'slug' => 'xvape-pod'],
        ['id' => 25, 'name' => 'XVAPE Disposable',          'slug' => 'xvape-disposable'],
        ['id' => 26, 'name' => 'Qirin Device',              'slug' => 'qirin-device'],
        ['id' => 27, 'name' => 'Qirin Pod',                 'slug' => 'qirin-pod'],
    ];

    Category::insert($categories);
    }
}
