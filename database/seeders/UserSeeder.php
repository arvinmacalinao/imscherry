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
                'username' => 'Admin',
                'email' => 'admin@admin.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'created_at' => now()
            ],
            [
                'name' => 'hazel',
                'username' => 'hazel',
                'email' => 'hazel@hazel.com',
                'email_verified_at' => now(),
                'password' => bcrypt('hazelpassword'),
                'created_at' => now()
            ],
            [
                'name' => 'pau',
                'username' => 'pau',
                'email' => 'pau@pau.com',
                'email_verified_at' => now(),
                'password' => bcrypt('paupassword'),
                'created_at' => now()
            ],
            [
                'name' => 'cherry',
                'username' => 'cherry',
                'email' => 'admcherryin@cherry.com',
                'email_verified_at' => now(),
                'password' => bcrypt('cherrypassword'),
                'created_at' => now()
            ],
            [
                'name' => 'dianne',
                'username' => 'dianne',
                'email' => 'dianne@dianne.com',
                'email_verified_at' => now(),
                'password' => bcrypt('diannepassword'),
                'created_at' => now()
            ],
            [
                'name' => 'may-ann',
                'username' => 'may-ann',
                'email' => 'may@may.com',
                'email_verified_at' => now(),
                'password' => bcrypt('maypassword'),
                'created_at' => now()
            ],
            [
                'name' => 'cha',
                'username' => 'cha',
                'email' => 'cha@cha.com',
                'email_verified_at' => now(),
                'password' => bcrypt('chapassworda'),
                'created_at' => now()
            ],
            [
                'name' => 'nancy',
                'username' => 'nancy',
                'email' => 'nancy@nancy.com',
                'email_verified_at' => now(),
                'password' => bcrypt('nancypassword'),
                'created_at' => now()
            ],
            [
                'name' => 'duinkie',
                'username' => 'duinkie',
                'email' => 'duinkie@duinkie.com',
                'email_verified_at' => now(),
                'password' => bcrypt('duinkiepassword'),
                'created_at' => now()
            ],
            [
                'name' => 'rox',
                'username' => 'rox',
                'email' => 'rox@rox.com',
                'email_verified_at' => now(),
                'password' => bcrypt('roxpassword'),
                'created_at' => now()
            ],
            [
                'name' => 'jess',
                'username' => 'jess',
                'email' => 'jess@jess.com',
                'email_verified_at' => now(),
                'password' => bcrypt('jesspassword'),
                'created_at' => now()
            ],
            [
                'name' => 'nicanor',
                'username' => 'nicanor',
                'email' => 'nicanor@nicanor.com',
                'email_verified_at' => now(),
                'password' => bcrypt('nicanorpassword'),
                'created_at' => now()
            ],
            [
                'name' => 'marvin',
                'username' => 'marvin',
                'email' => 'marvin@marvin.com',
                'email_verified_at' => now(),
                'password' => bcrypt('marvinpassword'),
                'created_at' => now()
            ],
            [
                'name' => 'alimoden',
                'username' => 'alimoden',
                'email' => 'alimoden@alimoden.com',
                'email_verified_at' => now(),
                'password' => bcrypt('alimodenpassword'),
                'created_at' => now()
            ],
            [
                'name' => 'ronnie',
                'username' => 'ronnie',
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
