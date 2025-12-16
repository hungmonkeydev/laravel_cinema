<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;

class CinemaCornerController extends Controller
{
    public function index($section)
    {
        // Lấy tất cả phim
        $movies = Movie::all();

        switch ($section) {
            case 'genres':
                // Logic: Lấy cột 'genre', tách dấu phẩy, đếm số lượng
                $stats = $movies->pluck('genre') 
                    ->filter() // Bỏ qua phim chưa nhập thể loại
                    ->map(fn($g) => explode(',', $g)) // Tách chuỗi bằng dấu phẩy
                    ->flatten() // Gộp thành 1 danh sách dài
                    ->map(fn($g) => trim($g)) // Xóa khoảng trắng thừa
                    ->countBy() // Đếm số lần xuất hiện
                    ->sortDesc() // Sắp xếp giảm dần
                    ->take(8); // Lấy top 8

                // Chuyển format để trả về Frontend
                return collect($stats)->map(fn($count, $name) => [
                    'name' => $name,
                    'count' => $count
                ])->values();

            case 'actors':
                // Logic tương tự cho Diễn viên (cột 'cast')
                $stats = $movies->pluck('cast')
                    ->filter()
                    ->map(fn($a) => explode(',', $a))
                    ->flatten()
                    ->map(fn($a) => trim($a))
                    ->countBy()
                    ->sortDesc()
                    ->take(8);

                return collect($stats)->map(fn($count, $name) => [
                    'name' => $name,
                    'movies' => $count
                ])->values();

            case 'directors':
                // Đạo diễn thường chỉ có 1 người, không cần tách mảng
                $stats = $movies->pluck('director')
                    ->filter()
                    ->map(fn($d) => trim($d))
                    ->countBy()
                    ->sortDesc()
                    ->take(8);

                return collect($stats)->map(fn($count, $name) => [
                    'name' => $name,
                    'movies' => $count
                ])->values();
            
            case 'reviews':
                // Trả về dữ liệu giả để không lỗi Frontend (vì chưa có bảng reviews)
                return [
                    [
                        'title' => 'Phim chưa có đánh giá',
                        'author' => 'Hệ thống',
                        'rating' => 5,
                        'excerpt' => 'Tính năng đang phát triển.'
                    ]
                ];

            case 'blog':
                // Lấy tin tức từ phim sắp chiếu
                return Movie::where('status', 'coming_soon')
                    ->orderByDesc('created_at')
                    ->take(4)
                    ->get()
                    ->map(fn($m) => [
                        'title' => "Sắp chiếu: " . $m->title,
                        'date' => $m->created_at ? $m->created_at->format('d/m/Y') : now()->format('d/m/Y'),
                        'views' => rand(100, 5000)
                    ]);

            default:
                return response()->json([], 404);
        }
    }
}