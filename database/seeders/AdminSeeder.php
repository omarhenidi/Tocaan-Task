<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'admin',
                'email_verified_at' => now(),
                'password' => Hash::make('123456'),
            ]
        );

        if (! $user->hasRole(UserRole::Admin)) {
            $user->assignRole(UserRole::Admin);
        }
    }
}
