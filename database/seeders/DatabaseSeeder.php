<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ... tus otros seeders
        // $this->call(BasicSeeder::class);
        // $this->call(ProductSeeder::class);
        // $this->call(AdminUserSeeder::class);

        // Evita duplicar test@example.com
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'), // o el que uses
                'email_verified_at' => now(),
            ]
        );
    }
}
