<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SICAT_INITIAL_ADMIN_EMAIL');
        $password = env('SICAT_INITIAL_ADMIN_PASSWORD');
        if (! $email || ! $password) {
            return;
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Administrador SICAT',
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'role' => 'admin',
                'status' => 'active',
            ]
        );
    }
}
