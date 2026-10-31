<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->warn('Development admin seeder skipped outside the local environment.');
            return;
        }

        $password = env('DEV_ADMIN_PASSWORD');
        if (! is_string($password) || strlen($password) < 12) {
            $this->command->warn('Set DEV_ADMIN_PASSWORD to a development-only value of at least 12 characters.');
            return;
        }

        User::create([
            'name' => 'Super Admin',
            'email' => env('DEV_ADMIN_EMAIL', 'admin@example.test'),
            'password' => Hash::make($password),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Development admin user created.');
    }
}
