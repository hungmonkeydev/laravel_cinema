<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;

class MovieController extends Controller
{
    public function index()
    {
        // SỬA LẠI TÊN CỘT CHO KHỚP DATABASE
        $movies = Movie::select(
            'movie_id',         // Để nguyên, Frontend của bạn đã update để nhận movie_id
            'title',
            'poster_url',       // Frontend đã update để nhận poster_url
            'age_rating as badge', // Đổi tên cho khớp frontend
            'genre',
            'duration',         // <--- QUAN TRỌNG: Database là 'duration', không phải 'duration_minutes'
            'director',
            'description',
            'release_date',     // Frontend đã update để nhận release_date
            'status',
            'rating'
        )->get();

        return response()->json($movies);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json([]);
        }

        $movies = Movie::where('title', 'like', "%{$query}%")
            ->orWhere('director', 'like', "%{$query}%") // Tìm thêm theo đạo diễn cho xịn
            ->select(
                'movie_id',
                'title',
                'poster_url',
                'age_rating as badge',
                'genre',
                'duration',      // <--- Sửa ở đây nữa
                'director',
                'description',
                'description_vi',
                'release_date',
                'rating',
                'status',
                'trailer_url',
                'cast'
            )
            ->take(10)
            ->get();

        return response()->json($movies);
    }
}
