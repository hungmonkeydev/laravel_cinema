<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Chạy các Seeder cơ bản trước
        $this->call([
            AdminSeeder::class,
            MovieSeeder::class, // Đảm bảo phim được tạo trước để lấy ID
        ]);

        // 2. Kiểm tra và tạo dữ liệu test cho Ghế & Suất chiếu
        if (Schema::hasTable('seats') && Schema::hasTable('showtimes') && Schema::hasTable('rooms')) {
            
            // --- BƯỚC A: Đảm bảo Phòng (Room) tồn tại ---
            // Nếu chưa có phòng ID=1, hãy tạo nó để tránh lỗi khóa ngoại
            $roomId = 1;
            $roomExists = DB::table('rooms')->where('room_id', $roomId)->exists(); // Giả sử khoá chính là room_id hoặc id
            
            if (!$roomExists) {
                 // Nếu bảng rooms dùng 'id' hay 'room_id' thì sửa lại key cho khớp nhé
                DB::table('rooms')->insertOrIgnore([
                    'room_id' => $roomId, 
                    'name' => 'Room 01',
                    'total_seats' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // --- BƯỚC B: Tạo Ghế (Seats) ---
            $rows = ['A', 'B', 'C', 'D', 'E'];
            foreach ($rows as $row) {
                for ($i = 1; $i <= 10; $i++) {
                    // Logic: Hàng E là VIP, còn lại là Regular
                    $type = ($row === 'E') ? 'VIP' : 'Regular'; 
                    $priceMultiplier = ($type === 'VIP') ? 1.25 : 1.00;

                    DB::table('seats')->insertOrIgnore([
                        'room_id' => $roomId,
                        'seat_row' => $row,
                        'seat_number' => $i,
                        'seat_type' => $type, // Sửa 'standard' thành 'Regular' cho khớp code cũ
                        'price_multiplier' => $priceMultiplier,
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // --- BƯỚC C: Tạo Suất chiếu (Showtime) ---
            // Lấy id của một bộ phim bất kỳ vừa seed ở trên
            $firstMovie = DB::table('movies')->first();

            if ($firstMovie && DB::table('showtimes')->count() === 0) {
                DB::table('showtimes')->insert([
                    'room_id' => $roomId,
                    'movie_id' => $firstMovie->movie_id, // SỬA: Thêm movie_id (bắt buộc)
                    'start_time' => Carbon::now()->addHours(2), // SỬA: Thêm giờ chiếu (bắt buộc)
                    'end_time' => Carbon::now()->addHours(4),   // SỬA: Thêm giờ kết thúc
                    'base_price' => 50000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}