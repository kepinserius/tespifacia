<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ])->assignRole('admin');

        // Create manager user
        User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ])->assignRole('manager');

        // Create regular user
        User::create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ])->assignRole('user');

        // Create additional sample users
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'uuid' => (string) Str::uuid(),
                'name' => "Test User {$i}",
                'email' => "test{$i}@example.com",
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
            ])->assignRole('user');
        }
    }
}
