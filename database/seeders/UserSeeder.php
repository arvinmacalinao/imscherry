<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = collect([
            [
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'created_at' => now()
            ],
            [
                'name' => 'hazel',
                'email' => 'hazel@hazel.com',
                'email_verified_at' => now(),
                'password' => bcrypt('hazelpassword'),
                'created_at' => now()
            ],
            [
                'name' => 'pau',
                'email' => 'pau@pau.com',
                'email_verified_at' => now(),
                'password' => bcrypt('paupassword'),
                'created_at' => now()
            ],
            [
                'name' => 'cherry',
                'email' => 'admcherryin@cherry.com',
                'email_verified_at' => now(),
                'password' => bcrypt('cherrypassword'),
                'created_at' => now()
            ],
            [
                'name' => 'dianne',
                'email' => 'dianne@dianne.com',
                'email_verified_at' => now(),
                'password' => bcrypt('diannepassword'),
                'created_at' => now()
            ],
            [
                'name' => 'may-ann',
                'email' => 'may@may.com',
                'email_verified_at' => now(),
                'password' => bcrypt('maypassword'),
                'created_at' => now()
            ],
            [
                'name' => 'cha',
                'email' => 'cha@cha.com',
                'email_verified_at' => now(),
                'password' => bcrypt('chapassworda'),
                'created_at' => now()
            ],
            [
                'name' => 'nancy',
                'email' => 'nancy@nancy.com',
                'email_verified_at' => now(),
                'password' => bcrypt('nancypassword'),
                'created_at' => now()
            ],
            [
                'name' => 'duinkie',
                'email' => 'duinkie@duinkie.com',
                'email_verified_at' => now(),
                'password' => bcrypt('duinkiepassword'),
                'created_at' => now()
            ],
            [
                'name' => 'rox',
                'email' => 'rox@rox.com',
                'email_verified_at' => now(),
                'password' => bcrypt('roxpassword'),
                'created_at' => now()
            ],
            [
                'name' => 'jess',
                'email' => 'jess@jess.com',
                'email_verified_at' => now(),
                'password' => bcrypt('jesspassword'),
                'created_at' => now()
            ],
            [
                'name' => 'nicanor',
                'email' => 'nicanor@nicanor.com',
                'email_verified_at' => now(),
                'password' => bcrypt('nicanorpassword'),
                'created_at' => now()
            ],
            [
                'name' => 'marvin',
                'email' => 'marvin@marvin.com',
                'email_verified_at' => now(),
                'password' => bcrypt('marvinpassword'),
                'created_at' => now()
            ],
            [
                'name' => 'alimoden',
                'email' => 'alimoden@alimoden.com',
                'email_verified_at' => now(),
                'password' => bcrypt('alimodenpassword'),
                'created_at' => now()
            ],
            [
                'name' => 'ronnie',
                'email' => 'ronnie@ronnie.com',
                'email_verified_at' => now(),
                'password' => bcrypt('ronniepassword'),
                'created_at' => now()
            ],
        ]);

        $users->each(function ($user){
            User::insert($user);
        });
    }
}
