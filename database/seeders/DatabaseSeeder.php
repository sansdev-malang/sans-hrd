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
                'name' => 'HRD',
                'password' => Hash::make('password'),
                'role' => 'hrd',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'superadmin1@sans.dev'],
            [
                'name' => 'Super Admin 1',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );

        User::firstOrCreate(
            ['email' => 'superadmin2@sans.dev'],
            [
                'name' => 'Super Admin 2',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'email_verified_at' => now(),
            ]
        );

        \App\Models\SchoolUnit::firstOrCreate(
            ['name' => 'PAUD'],
            [
                'api_url' => env('PAUD_API_URL', 'http://sans-paud.test/api/v1/hrd'),
                'api_token' => env('PAUD_API_TOKEN', 'rahasia_paud_123'),
                'is_active' => true,
            ]
        );

        \App\Models\SchoolUnit::firstOrCreate(
            ['name' => 'SD'],
            [
                'api_url' => env('SD_API_URL', 'http://sans-sd.test/api/v1/hrd'),
                'api_token' => env('SD_API_TOKEN', 'rahasia_sd_123'),
                'is_active' => true,
            ]
        );

        \App\Models\SchoolUnit::firstOrCreate(
            ['name' => 'SMP'],
            [
                'api_url' => env('SMP_API_URL', 'http://sans-smp.test/api/v1/hrd'),
                'api_token' => env('SMP_API_TOKEN', 'rahasia_smp_123'),
                'is_active' => true,
            ]
        );

        // $this->call(HrdDemoSeeder::class);
    }
}
