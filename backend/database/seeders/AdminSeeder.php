<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed the admin user from environment variables.
     */
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');
        $adminName = env('ADMIN_NAME', 'Administrator');

        if (empty($adminEmail) || empty($adminPassword)) {
            $this->command->error('ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env file');
            return;
        }

        if (User::where('email', $adminEmail)->exists()) {
            $this->command->warn("Admin user with email {$adminEmail} already exists. Skipping...");
            return;
        }

        User::create([
            'full_name' => $adminName,
            'email' => $adminEmail,
            'password' => $adminPassword,
            'phone' => null,
            'role' => 'admin',
        ]);

        $this->command->info("Admin user created successfully with email: {$adminEmail}");
    }
}
