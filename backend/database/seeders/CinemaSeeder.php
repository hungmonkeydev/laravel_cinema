<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CinemaSeeder extends Seeder
{
    public function run()
    {
        $cinemas = [
            "Galaxy Nguyễn Du",
            "Galaxy Sala",
            "Galaxy Tân Bình",
            "Galaxy Kinh Dương Vương",
            "Galaxy Quang Trung",
            "Galaxy Bến Tre",
            "Galaxy Mipec Long Biên",
            "Galaxy Đà Nẵng",
            "Galaxy Cà Mau",
        ];

        foreach ($cinemas as $name) {
            // Logic tự động đoán thành phố dựa trên tên rạp
            $city = 'TP.HCM'; // Mặc định
            $address = 'Địa chỉ đang cập nhật';

            if (str_contains($name, 'Đà Nẵng')) {
                $city = 'Đà Nẵng';
                $address = 'Tầng 3 Coop Mart, 478 Điện Biên Phủ, Đà Nẵng';
            } elseif (str_contains($name, 'Cà Mau')) {
                $city = 'Cà Mau';
                $address = 'Lầu 2 Sense City, 9 Trần Hưng Đạo, Cà Mau';
            } elseif (str_contains($name, 'Bến Tre')) {
                $city = 'Bến Tre';
                $address = 'Võ Nguyên Giáp, P.6, TP Bến Tre';
            } elseif (str_contains($name, 'Long Biên')) {
                $city = 'Hà Nội';
                $address = 'Tầng 6, Mipec Riverside, Long Biên, Hà Nội';
            } elseif (str_contains($name, 'Nguyễn Du')) {
                $address = '116 Nguyễn Du, Quận 1, TP.HCM';
            } elseif (str_contains($name, 'Tân Bình')) {
                $address = '246 Nguyễn Hồng Đào, Tân Bình, TP.HCM';
            } elseif (str_contains($name, 'Kinh Dương Vương')) {
                $address = '718bis Kinh Dương Vương, Q.6, TP.HCM';
            } elseif (str_contains($name, 'Quang Trung')) {
                $address = '304A Quang Trung, Gò Vấp, TP.HCM';
            } elseif (str_contains($name, 'Sala')) {
                $address = 'Tầng 3, Thiso Mall Sala, Thủ Đức, TP.HCM';
            }

            // Kiểm tra trùng lặp trước khi thêm
            if (DB::table('cinemas')->where('name', $name)->exists()) {
                continue;
            }

            DB::table('cinemas')->insert([
                'name' => $name,
                'address' => $address,
                'city' => $city,
                'phone' => '1900' . rand(1000, 9999), // Tạo số điện thoại giả
                'is_active' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}