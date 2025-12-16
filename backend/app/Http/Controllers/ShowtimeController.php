<?php

namespace App\Http\Controllers; // Lưu ý Namespace phải có chữ Api

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShowtimeController extends Controller
{
    public function getShowtimes(Request $request)
    {
        $movieId = $request->input('movie_id');
        $cinemaName = $request->input('cinema');

        if (!$movieId || !$cinemaName) {
            return response()->json([]);
        }

        // Truy vấn bảng showtimes, join với rooms và cinemas để lọc đúng rạp
        $showtimes = DB::table('showtimes')
            ->join('rooms', 'showtimes.room_id', '=', 'rooms.room_id')
            ->join('cinemas', 'rooms.cinema_id', '=', 'cinemas.cinema_id')
            ->where('showtimes.movie_id', $movieId)
            ->where('cinemas.name', $cinemaName)
            // Chỉ lấy các suất chiếu chưa diễn ra (tính từ hiện tại)
            ->where('showtimes.start_time', '>=', Carbon::now()) 
            ->select('showtimes.start_time')
            ->orderBy('showtimes.start_time')
            ->get();

        // Format dữ liệu trả về chỉ lấy giờ:phút (VD: 09:30)
        // unique() để loại bỏ các giờ trùng nhau (nếu rạp có nhiều phòng chiếu cùng giờ)
        $formattedTimes = $showtimes->map(function ($item) {
            return Carbon::parse($item->start_time)->format('H:i');
        })->unique()->values();

        return response()->json($formattedTimes);
    }
}