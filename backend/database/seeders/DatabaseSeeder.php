<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::insert([
            [
                'name' => 'User1',
                'email' => 'User1@gmail.com',
                'password' => bcrypt('123456'),
                'created_at' => date('Y-m-d H:i:s'),
                'update_at' => date('Y-m-d H:i:s')

            ],
            [
                'name' => 'User2',
                'email' => 'User2@gmail.com',
                'password' => bcrypt('123456'),
                'created_at' => date('Y-m-d H:i:s'),
                'update' => date('Y-m-d H:i:s')

            ]

        ]);
    }
}
