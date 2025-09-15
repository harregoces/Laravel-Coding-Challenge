<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Ada Lovelace', 'email' => 'ada@example.com'],
            ['name' => 'Alan Turing', 'email' => 'alan@example.com'],
            ['name' => 'Grace Hopper', 'email' => 'grace@example.com'],
        ];

        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                ['name' => $u['name'], 'password' => Hash::make('Password123!')]
            );
        }
    }
}
