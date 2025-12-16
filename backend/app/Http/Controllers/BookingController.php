<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    // Cấu hình VNPAY
    private $vnpTmnCode;
    private $vnpHashSecret;
    private $vnpUrl;
    private $vnpReturnUrl;

    public function __construct()
    {
        // Chỉ giữ lại config VNPAY
        $this->vnpTmnCode = env('VNPAY_TMN_CODE', 'VNPAY_TMN_CODE');
        $this->vnpHashSecret = env('VNPAY_HASH_SECRET', 'VNPAY_HASH_SECRET');
        $this->vnpUrl = env('VNPAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $this->vnpReturnUrl = env('VNPAY_RETURN_URL', 'http://localhost:3000/booking-success');
    }

    public function createBooking(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'showtime_id' => 'required|integer',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer'
        ]);

        $userId = $request->user_id;
        $showtimeId = $request->showtime_id;
        $seatIds = $request->seat_ids;

        try {
            return DB::transaction(function () use ($userId, $showtimeId, $seatIds) {

                // 1. CHECK & LOCK GHẾ (Tránh trùng)
                $conflictingSeats = DB::table('seat_status')
                    ->where('showtime_id', $showtimeId)
                    ->whereIn('seat_id', $seatIds)
                    ->where(function ($query) {
                        $query->where('status', 'booked')
                            ->orWhere(function ($q) {
                                $q->where('status', 'held')
                                    ->where('held_until', '>', Carbon::now());
                            });
                    })
                    ->lockForUpdate()
                    ->pluck('seat_id')
                    ->toArray();

                if (!empty($conflictingSeats)) {
                    return response()->json([
                        'message' => 'Ghế đã có người chọn.',
                        'conflicts' => $conflictingSeats
                    ], 409);
                }

                $showtime = DB::table('showtimes')->where('showtime_id', $showtimeId)->first();

                if (!$showtime) {
                    return response()->json(['message' => 'Showtime not found.'], 404);
                }

                $basePrice = $showtime->base_price ?? 0;
                $totalAmount = $basePrice * count($seatIds);

                // Tạo mã đơn hàng
                $bookingCode = 'BOOK' . time() . rand(100, 999);

                // 2. TẠO BOOKING
                $bookingId = DB::table('bookings')->insertGetId([
                    'user_id' => $userId,
                    'showtime_id' => $showtimeId,
                    'booking_code' => $bookingCode,
                    'status' => 'pending',
                    'total_amount' => $totalAmount,
                    'final_amount' => $totalAmount,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // 3. GIỮ GHẾ
                foreach ($seatIds as $seatId) {
                    DB::table('booking_seats')->insert([
                        'booking_id' => $bookingId,
                        'seat_id' => $seatId,
                        'price' => $basePrice,
                        'created_at' => Carbon::now()
                    ]);

                    DB::table('seat_status')->updateOrInsert(
                        ['showtime_id' => $showtimeId, 'seat_id' => $seatId],
                        [
                            'status' => 'held',
                            'held_by_user_id' => $userId,
                            'held_until' => Carbon::now()->addMinutes(10), // Giữ ghế 10 phút
                            'updated_at' => Carbon::now()
                        ]
                    );
                }

                // 4. GỌI PAYMENT GATEWAY (Mặc định VNPAY)
                // Chỉ xử lý VNPAY, nếu gửi method khác sẽ mặc định dùng VNPAY hoặc báo lỗi tùy bạn
                $paymentMethod = 'vnpay'; 
                
                $gatewayResponse = $this->createVnpayPayment($bookingCode, $totalAmount, "Thanh toan ve " . $bookingCode);
                $payUrl = is_array($gatewayResponse) ? ($gatewayResponse['payUrl'] ?? null) : null;

                return response()->json([
                    'status' => 'success',
                    'booking_id' => $bookingId,
                    'payUrl' => $payUrl,
                    'gateway_response' => $gatewayResponse,
                    'payment_method' => $paymentMethod,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // Hàm tạo link thanh toán VNPAY
    private function createVnpayPayment($orderId, $amount, $orderInfo)
    {
        // VNPAY tính tiền theo đơn vị đồng * 100
        $vnpAmount = (string) ($amount * 100);

        $vnpParams = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $this->vnpTmnCode,
            'vnp_Amount' => $vnpAmount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => Carbon::now()->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => request()->ip(),
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => $orderInfo,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $this->vnpReturnUrl,
            'vnp_TxnRef' => $orderId,
        ];

        // Sắp xếp tham số theo a-z để tạo mã hash
        ksort($vnpParams);

        $query = http_build_query($vnpParams);
        $hashData = urldecode($query);
        $secureHash = hash_hmac('sha512', $hashData, $this->vnpHashSecret);

        $payUrl = rtrim($this->vnpUrl, '?') . '?' . $query . '&vnp_SecureHash=' . $secureHash;

        return ['payUrl' => $payUrl, 'params' => $vnpParams];
    }

    /**
     * Get all bookings for admin (with user info)
     */
    public function adminIndex()
    {
        try {
            $bookings = DB::table('bookings')
                ->join('users', 'bookings.user_id', '=', 'users.user_id')
                ->select(
                    'bookings.booking_id',
                    'bookings.booking_code',
                    'bookings.status',
                    'bookings.total_amount',
                    'bookings.final_amount',
                    'bookings.created_at',
                    'bookings.updated_at',
                    'users.full_name as user_name',
                    'users.email as user_email',
                    'users.phone as user_phone'
                )
                ->orderBy('bookings.created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $bookings,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [],
            ], 200);
        }
    }

    /**
     * Get booking details for admin (with seats info)
     */
    public function adminShow($id)
    {
        try {
            $booking = DB::table('bookings')
                ->join('users', 'bookings.user_id', '=', 'users.user_id')
                ->where('bookings.booking_id', $id)
                ->select(
                    'bookings.*',
                    'users.full_name as user_name',
                    'users.email as user_email',
                    'users.phone as user_phone'
                )
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found.'
                ], 404);
            }

            // Get booking seats
            $seats = DB::table('booking_seats')
                ->join('seats', 'booking_seats.seat_id', '=', 'seats.seat_id')
                ->where('booking_seats.booking_id', $id)
                ->select('seats.*', 'booking_seats.price')
                ->get();

            $booking->seats = $seats;

            return response()->json([
                'success' => true,
                'data' => $booking,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching booking: ' . $e->getMessage()
            ], 500);
        }
    }
}