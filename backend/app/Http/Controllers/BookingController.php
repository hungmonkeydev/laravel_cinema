<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    // LƯU Ý: Lấy thông tin cấu hình từ file .env để không lưu secret trong mã nguồn
    private $partnerCode;
    private $accessKey;
    private $secretKey;
    private $vnpTmnCode;
    private $vnpHashSecret;
    private $vnpUrl;
    private $vnpReturnUrl;

    public function __construct()
    {
        $this->partnerCode = env('MOMO_PARTNER_CODE', 'MOMO_PARTNER_CODE');
        $this->accessKey = env('MOMO_ACCESS_KEY', 'MOMO_ACCESS_KEY');
        $this->secretKey = env('MOMO_SECRET_KEY', 'MOMO_SECRET_KEY');
        // VNPAY config
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

                // Tạo mã đơn hàng (Dùng luôn mã này gửi qua Momo để dễ tìm lại)
                $bookingCode = 'BOOK' . time() . rand(100, 999);

                // 2. TẠO BOOKING
                $bookingId = DB::table('bookings')->insertGetId([
                    'user_id' => $userId,
                    'showtime_id' => $showtimeId,
                    'booking_code' => $bookingCode, // Đây sẽ là orderId của Momo
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
                            'held_until' => Carbon::now()->addMinutes(10),
                            'updated_at' => Carbon::now()
                        ]
                    );
                }

                // 4. GỌI PAYMENT GATEWAY THEO payment_method
                $paymentMethod = request()->input('payment_method', 'momo');
                $payUrl = null;
                $gatewayResponse = null;

                if ($paymentMethod === 'vnpay') {
                    $gatewayResponse = $this->createVnpayPayment($bookingCode, $totalAmount, "Thanh toan ve " . $bookingCode);
                    $payUrl = is_array($gatewayResponse) ? ($gatewayResponse['payUrl'] ?? null) : null;
                } else {
                    $gatewayResponse = $this->createMomoPayment($bookingCode, $totalAmount, "Thanh toan ve " . $bookingCode);
                    $payUrl = is_array($gatewayResponse) ? ($gatewayResponse['payUrl'] ?? null) : null;

                    // Nếu không có payUrl, cho phép trả mock payUrl khi đang dev hoặc khi env bật mock
                    $useMock = env('MOMO_USE_MOCK', false);
                    if (empty($payUrl) && ($useMock || app()->environment('local'))) {
                        $mockBase = env('MOMO_MOCK_PAYURL', 'http://localhost:3000/mock-payment');
                        $payUrl = $mockBase . '?order=' . urlencode($bookingCode) . '&amount=' . urlencode((string)$totalAmount);
                    }
                }

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

    // --- PHẦN QUAN TRỌNG: XỬ LÝ KẾT QUẢ TỪ MOMO ---
    public function momoIpn(Request $request)
    {
        $data = $request->all();

        // 1. Kiểm tra bảo mật (Bắt buộc)
        // Cho phép mock IPN trong môi trường dev nếu bật cấu hình
        $allowMock = env('MOMO_ALLOW_MOCK_IPN', false);
        if ($allowMock) {
            $signatureValid = true;
        } else {
            $signatureValid = $this->checkSignature($data);
        }

        if (!$signatureValid) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $resultCode = $data['resultCode'] ?? -1;
        $orderId = $data['orderId']; // Chính là booking_code mình gửi đi lúc nãy

        // Tìm đơn hàng trong DB
        $booking = DB::table('bookings')->where('booking_code', $orderId)->first();

        if (!$booking) return response()->json(['message' => 'Order not found'], 404);
        if ($booking->status == 'paid') return response()->json(['message' => 'Already paid'], 204);

        if ($resultCode == 0) {
            // --- THANH TOÁN THÀNH CÔNG ---
            DB::transaction(function () use ($booking) {
                // A. Đổi trạng thái đơn hàng
                DB::table('bookings')
                    ->where('booking_id', $booking->booking_id)
                    ->update(['status' => 'paid', 'updated_at' => Carbon::now()]);

                // B. Lấy danh sách ghế của đơn này
                $seatIds = DB::table('booking_seats')
                    ->where('booking_id', $booking->booking_id)
                    ->pluck('seat_id');

                // C. Chốt ghế vĩnh viễn (Booked)
                DB::table('seat_status')
                    ->where('showtime_id', $booking->showtime_id)
                    ->whereIn('seat_id', $seatIds)
                    ->update([
                        'status' => 'booked',
                        'held_until' => null, // Xóa thời gian hết hạn
                        'updated_at' => Carbon::now()
                    ]);
            });

            return response()->json(['message' => 'Payment Success'], 204);
        } else {
            // --- THANH TOÁN THẤT BẠI / HỦY ---
            DB::transaction(function () use ($booking) {
                // A. Hủy đơn
                DB::table('bookings')
                    ->where('booking_id', $booking->booking_id)
                    ->update(['status' => 'cancelled', 'updated_at' => Carbon::now()]);

                // B. Nhả ghế ra (Available)
                $seatIds = DB::table('booking_seats')
                    ->where('booking_id', $booking->booking_id)
                    ->pluck('seat_id');

                DB::table('seat_status')
                    ->where('showtime_id', $booking->showtime_id)
                    ->whereIn('seat_id', $seatIds)
                    ->update([
                        'status' => 'available',
                        'held_until' => null,
                        'held_by_user_id' => null
                    ]);
            });

            return response()->json(['message' => 'Payment Failed'], 204);
        }
    }

    // Hàm tạo link thanh toán
    private function createMomoPayment($orderId, $amount, $orderInfo)
    {
        // ... (Giữ nguyên logic cũ của bạn, nhớ thay ipnUrl bằng link Ngrok/Public)
        // Lưu ý: amount phải là string, không có dấu phẩy (VD: "50000")
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $requestId = (string) Str::uuid();

        // Redirect URL và IPN URL nên được cấu hình trong .env (ngrok hoặc public URL khi dev)
        $redirectUrl = env('MOMO_REDIRECT_URL', 'http://localhost:3000/booking-success');
        $ipnUrl = env('MOMO_IPN_URL', 'https://your-ngrok-url.ngrok.io/api/payment/momo-ipn');

        $requestType = "captureWallet";
        $extraData = "";

        $rawHash = "accessKey=" . $this->accessKey .
            "&amount=" . (string)$amount .
            "&extraData=" . $extraData .
            "&ipnUrl=" . $ipnUrl .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&partnerCode=" . $this->partnerCode .
            "&redirectUrl=" . $redirectUrl .
            "&requestId=" . $requestId .
            "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        try {
            $response = Http::post($endpoint, [
                'partnerCode' => $this->partnerCode,
                'partnerName' => "Rap Phim Demo",
                'storeId' => "MomoTestStore",
                'requestId' => $requestId,
                'amount' => (string)$amount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $redirectUrl,
                'ipnUrl' => $ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature
            ]);

            return $response->json();
        } catch (\Exception $e) {
            // Trả về thông tin lỗi để frontend có thể debug (không expose secret)
            return ['error' => $e->getMessage()];
        }
    }

    // Hàm tạo link thanh toán VNPAY
    private function createVnpayPayment($orderId, $amount, $orderInfo)
    {
        // amount: VND (integer). VNPAY often expects amount in smallest unit; multiply by 100 to be safe if needed.
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

        // Sort params by key
        ksort($vnpParams);

        $query = http_build_query($vnpParams);
        // VNPAY requires hash of the query string
        $hashData = urldecode($query);
        $secureHash = hash_hmac('sha512', $hashData, $this->vnpHashSecret);

        $payUrl = rtrim($this->vnpUrl, '?') . '?' . $query . '&vnp_SecureHash=' . $secureHash;

        return ['payUrl' => $payUrl, 'params' => $vnpParams];
    }

    // Hàm kiểm tra chữ ký bảo mật từ Momo gửi về
    private function checkSignature($data)
    {
        if (!isset($data['signature'])) return false;

        // Use null coalescing to avoid undefined index notices
        $amount = $data['amount'] ?? '';
        $extraData = $data['extraData'] ?? '';
        $message = $data['message'] ?? '';
        $orderId = $data['orderId'] ?? '';
        $orderInfo = $data['orderInfo'] ?? '';
        $orderType = $data['orderType'] ?? '';
        $partnerCode = $data['partnerCode'] ?? '';
        $payType = $data['payType'] ?? '';
        $requestId = $data['requestId'] ?? '';
        $responseTime = $data['responseTime'] ?? '';
        $resultCode = $data['resultCode'] ?? '';
        $transId = $data['transId'] ?? '';

        // Dữ liệu Momo gửi về (cần sắp xếp đúng thứ tự này để hash)
        $rawHash = "accessKey=" . $this->accessKey .
            "&amount=" . $amount .
            "&extraData=" . $extraData .
            "&message=" . $message .
            "&orderId=" . $orderId .
            "&orderInfo=" . $orderInfo .
            "&orderType=" . $orderType .
            "&partnerCode=" . $partnerCode .
            "&payType=" . $payType .
            "&requestId=" . $requestId .
            "&responseTime=" . $responseTime .
            "&resultCode=" . $resultCode .
            "&transId=" . $transId;

        $mySignature = hash_hmac("sha256", $rawHash, $this->secretKey);

        return $mySignature === $data['signature'];
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
            // Table doesn't exist yet, return empty array
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
