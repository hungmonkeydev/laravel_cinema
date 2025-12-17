<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MovieSeeder extends Seeder
{
    public function run()
    {
        // SỬA TỪ 'insert' THÀNH 'updateOrInsert'
        DB::table('movies')->updateOrInsert(
            // 1. Điều kiện kiểm tra (Nếu trùng tên phim này thì không tạo mới)
            ['title' => 'IMAX Treasure Hunt'], 
            
            // 2. Dữ liệu (Nếu chưa có thì thêm, có rồi thì cập nhật mấy cái này)
            [
                'title_vi' => 'Chuyến săn kho báu IMAX',
                'description' => 'An exciting IMAX adventure.',
                'description_vi' => 'Cuộc phiêu lưu hấp dẫn trên màn ảnh IMAX.',
                'genre' => 'Adventure',
                'duration_minutes' => 120,
                'release_date' => '2025-12-25',
                'director' => 'Jane Doe',
                'cast' => 'Actor A, Actor B',
                'rating' => 8.5,
                'poster_url' => '/poster/imax-treasure-hunt-movie-poster.jpg',
                'trailer_url' => '',
                'status' => 'showing',
                'language' => 'Vietnamese',
                'age_rating' => 'P',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}