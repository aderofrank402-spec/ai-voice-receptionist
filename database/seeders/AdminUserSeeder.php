<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
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

        $email = env('DEV_ADMIN_EMAIL');
        if (! is_string($email) || $email === '') {
            $this->command->warn('Set DEV_ADMIN_EMAIL before running this seeder.');
            return;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update(['is_admin' => true]);
            $this->command->info('The configured development user is now an administrator.');
        } else {
            $this->command->error('The configured development user was not found.');
        }
    }
}
