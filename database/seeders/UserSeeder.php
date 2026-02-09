<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::create([
            'name' => 'User 1',
            'email' => 'user1@test.com',
            'password' => bcrypt('password'),
            'role' => 1,
            'status' => 1,
            'is_mail_verified' => 1,
        ]);

        User::create([
            'name' => 'User 2',
            'email' => 'user2@test.com',
            'password' => bcrypt('password'),
            'role' => 1,
            'status' => 1,
            'is_mail_verified' => 1,
        ]);
    }
}
