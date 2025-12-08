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
    // database/seeders/UserSeeder.php

public function run(): void
{
    // Cột trong DB: user_id, full_name, email, phone, password_hash, role, is_active, created_at, updated_at

    User::insert([
        [
            // 1. Dùng user_id (Không cần vì là AUTO_INCREMENT)
            
            // 2. Tên cột: full_name (Thay cho 'name' cũ)
            'full_name' => 'Nguyễn Văn A', 
            
            // 3. email
            'email' => 'nguyena@example.com',
            
            // 4. phone (Có thể NULL)
            'phone' => '0901234567', 
            
            // 5. Tên cột: password_hash (Thay cho 'password' cũ)
            'password_hash' => bcrypt('123456789'), 
            
            // 6. role (Mặc định 'customer')
            'role' => 'customer', 
            
            // 7. is_active (Mặc định 1)
            'is_active' => 1,
            
            // 8. created_at
            'created_at' => now(),
            
            // 9. updated_at
            'updated_at' => now(), 

            // LƯU Ý: Cột 'email_verified_at' và 'remember_token' đã bị xóa khỏi DB của bạn nên không cần chèn.
        ],
        [
            'full_name' => 'Admin Chính',
            'email' => 'admin@example.com',
            'phone' => '0987654321',
            'password_hash' => bcrypt('adminpass'),
            'role' => 'admin', // Thêm một user có quyền admin
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);
}
}
