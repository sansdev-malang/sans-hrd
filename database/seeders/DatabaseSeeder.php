<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'hrd@sans.dev'],
            [
                'name' => 'HRD Central',
                'password' => Hash::make('password'),
                'role' => 'hrd',
                'email_verified_at' => now(),
            ]
        );

        \App\Models\SchoolUnit::firstOrCreate(
            ['name' => 'SD Unit'],
            [
                'api_url' => env('SD_API_URL', 'http://sansdev.test/api/v1/hrd'),
                'api_token' => env('SD_API_TOKEN', 'rahasia_sd_123'),
                'is_active' => true,
            ]
        );
    }
}
