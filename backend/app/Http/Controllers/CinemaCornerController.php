<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;

class CinemaCornerController extends Controller
{
    public function index($section)
    {
        $movies = Movie::all();

        switch ($section) {
            case 'genres':
                // Tách chuỗi thể loại và đếm
                $data = $movies->pluck('genre')
                    ->filter()
                    ->map(fn($item) => explode(',', $item)) // Tách dấu phẩy
                    ->flatten()
                    ->map(fn($item) => trim($item)) // Xóa khoảng trắng
                    ->countBy() // Đếm số lượng
                    ->sortDesc()
                    ->take(8);

                // Format lại dữ liệu cho Frontend
                return collect($data)->map(fn($count, $name) => [
                    'name' => $name,
                    'count' => $count
                ])->values();

            case 'actors':
                // Tách chuỗi diễn viên (cột cast)
                $data = $movies->pluck('cast')
                    ->filter()
                    ->map(fn($item) => explode(',', $item))
                    ->flatten()
                    ->map(fn($item) => trim($item))
                    ->countBy()
                    ->sortDesc()
                    ->take(8);

                return collect($data)->map(fn($count, $name) => [
                    'name' => $name,
                    'movies' => $count
                ])->values();

            case 'directors':
                // Đạo diễn (thường 1 người nên không cần tách mảng)
                $data = $movies->pluck('director')
                    ->filter()
                    ->map(fn($item) => trim($item))
                    ->countBy()
                    ->sortDesc()
                    ->take(8);

                return collect($data)->map(fn($count, $name) => [
                    'name' => $name,
                    'movies' => $count
                ])->values();

            case 'blog':
                // Lấy phim sắp chiếu làm tin tức giả lập
                return Movie::where('status', 'coming_soon')
                    ->orderByDesc('created_at')
                    ->take(4)
                    ->get()
                    ->map(fn($m) => [
                        'title' => "Sắp ra mắt: " . $m->title,
                        'date' => $m->created_at ? $m->created_at->format('d/m/Y') : now()->format('d/m/Y'),
                        'views' => rand(1000, 5000)
                    ]);

            case 'reviews':
                // Dữ liệu giả vì chưa có bảng Reviews (bạn có thể tạo bảng sau)
                return [
                    [
                        'title' => 'Chưa có dữ liệu thật',
                        'author' => 'Admin',
                        'rating' => 5,
                        'excerpt' => 'Tính năng Review cần tạo thêm bảng trong database.'
                    ]
                ];

            default:
                return response()->json([], 404);
        }
    }
}
