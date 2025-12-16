<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MovieSeeder extends Seeder
{
    public function run()
    {
        DB::table('movies')->insert([
            'title' => 'IMAX Treasure Hunt',
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
        ]);
    }
}
