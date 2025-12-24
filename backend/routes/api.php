<?php

use App\Http\Controllers\Api\VnpayController as ApiVnpayController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\CinemaCornerController;
use App\Http\Controllers\SeatController;
use App\Http\Controllers\ShowtimeController;
use App\Http\Controllers\VnpayController;

/*
|--------------------------------------------------------------------------
| API Routes (Định nghĩa Route API)
|--------------------------------------------------------------------------
*/

// --- ROUTE CÔNG KHAI (KHÔNG CẦN ĐĂNG NHẬP) ---

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// OTP Routes
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('resend-otp', [AuthController::class, 'resendOtp']);

// Google OAuth Routes
Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Public Movie & Cinema Routes
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/search', [MovieController::class, 'search']);
Route::get('/cinema-corner/{section}', [CinemaCornerController::class, 'index']);
Route::get('/showtimes/{id}/seats', [SeatController::class, 'getSeatsByShowtime']);
Route::get('/showtimes', [ShowtimeController::class, 'getShowtimes']);

// API Webhook (Momo/VNPay gọi ngược lại server - Phải để Public)
Route::post('/payment/momo-ipn', [BookingController::class, 'momoIpn']);

// Test Routes (Có thể xóa sau này)
Route::get('test/user-schema', function () { /* ... */ });
Route::get('test/email-config', function () { /* ... */ });
Route::get('test/send-email', function () { /* ... */ });


// --- ROUTE BẢO MẬT (BẮT BUỘC PHẢI CÓ TOKEN) ---
// 🔥 QUAN TRỌNG: Tất cả API cần đăng nhập phải ném vào trong nhóm này
Route::middleware('auth:sanctum')->group(function () {
    
    // 1. Lấy thông tin & Đăng xuất
    Route::get('user', [AuthController::class, 'showAuthenticatedUser']);
    Route::post('logout', [AuthController::class, 'logout']);

    // 2. 🔥 API THANH TOÁN (Đã chuyển vào đây)
    // Bây giờ nếu không có Token, Laravel sẽ trả về lỗi 401 Unauthorized ngay
    Route::post('/vnpay_payment', [VnpayController::class, 'createPayment']);

    // 3. API Đặt vé (Nên bảo mật luôn nếu cần lưu user_id)
    Route::post('/booking/create', [BookingController::class, 'createBooking']);
});


// --- ADMIN ROUTES (Chỉ Admin mới vào được) ---
Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('movies', App\Http\Controllers\Admin\MovieController::class);
    Route::get('/bookings', [BookingController::class, 'adminIndex']);
    Route::get('/bookings/{id}', [BookingController::class, 'adminShow']);
});