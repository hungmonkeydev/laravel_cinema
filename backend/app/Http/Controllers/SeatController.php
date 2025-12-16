<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Thư viện xử lý thời gian

class SeatController extends Controller
{
    public function getSeatsByShowtime($showtimeId)
    {
        // 1. Lấy thông tin suất chiếu
        $showtime = DB::table('showtimes')
            ->where('showtime_id', $showtimeId)
            ->first();

        if (!$showtime) {
            return response()->json(['message' => 'Suất chiếu không tồn tại'], 404);
        }

        // 2. Lấy danh sách ghế từ bảng 'seats' (như cũ)
        $seats = DB::table('seats')
            ->where('room_id', $showtime->room_id)
            ->where('is_active', 1) 
            ->orderBy('seat_row')
            ->orderBy('seat_number')
            ->get();

        // 3. XỬ LÝ TRẠNG THÁI (Logic mới dựa trên bảng seat_status)
        $now = Carbon::now(); // Lấy thời gian hiện tại

        // Lấy danh sách ghế đang KHÔNG có sẵn (Booked hoặc đang Hold còn hạn)
        $occupiedSeats = DB::table('seat_status')
            ->where('showtime_id', $showtimeId)
            ->where(function ($query) use ($now) {
                $query->where('status', 'booked') // Ghế đã bán
                      ->orWhere(function ($q) use ($now) {
                          // Hoặc ghế đang giữ NHƯNG chưa hết hạn
                          $q->where('status', 'hold') // Giả sử enum của bạn là 'hold' hoặc 'held'
                            ->where('held_until', '>', $now); 
                      });
            })
            ->pluck('status', 'seat_id'); // [seat_id => status]

        // 4. Map dữ liệu
        $seatMap = $seats->map(function ($seat) use ($occupiedSeats, $showtime) {
            $finalPrice = $showtime->base_price * $seat->price_multiplier;
            
            // Mặc định là available
            $status = 'available';

            // Kiểm tra xem ghế có nằm trong danh sách "đang bận" không
            if (isset($occupiedSeats[$seat->seat_id])) {
                $status = $occupiedSeats[$seat->seat_id];
                // Nếu status là 'hold', frontend sẽ hiển thị màu vàng/xám tùy logic
            }

            return [
                'id' => $seat->seat_id,
                'row' => $seat->seat_row,
                'number' => $seat->seat_number,
                'type' => $seat->seat_type,
                'price' => $finalPrice,
                'status' => $status, 
            ];
        });

        // 5. Group theo hàng
        $groupedSeats = $seatMap->groupBy('row');

        return response()->json([
            'room_id' => $showtime->room_id,
            'base_price' => $showtime->base_price,
            'seats' => $groupedSeats
        ]);
    }
}