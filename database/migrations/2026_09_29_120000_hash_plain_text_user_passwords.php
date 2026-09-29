<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Users created on the Users page had their password saved as plain text (UserController@store
     * is fixed), so they could not log in. Hash those passwords: the same password now works.
     * Already-hashed passwords (bcrypt "$2y$" / argon "$argon") are left alone.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('password')
            ->where('password', '<>', '')
            ->where('password', 'not like', '$2y$%')
            ->where('password', 'not like', '$argon%')
            ->orderBy('id')
            ->select('id', 'password')
            ->get()
            ->each(fn ($user) => DB::table('users')
                ->where('id', $user->id)
                ->update(['password' => Hash::make($user->password)]));
    }

    public function down(): void
    {
        // hashing cannot be undone
    }
};
