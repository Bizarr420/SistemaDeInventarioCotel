<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            BasicSeeder::class,
            ProductSeeder::class,
            AdminUserSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Usuario de prueba',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}
