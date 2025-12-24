<?php

// Import các lớp cần thiết cho việc định nghĩa route và Controller.

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

// --- ROUTE XÁC THỰC (AUTHENTICATION ROUTES) ---
// Route đăng ký
Route::post('register', [AuthController::class, 'register']); // Frontend gọi /api/register
// Route đăng nhập
Route::post('login', [AuthController::class, 'login']);   // Frontend gọi /api/login

// OTP Routes
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('resend-otp', [AuthController::class, 'resendOtp']);

// Google OAuth Routes
Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback']);

// Test route to check user schema
Route::get('test/user-schema', function () {
    $user = \App\Models\User::first();
    return response()->json([
        'has_user' => $user ? true : false,
        'fillable' => (new \App\Models\User())->getFillable(),
        'columns' => $user ? array_keys($user->getAttributes()) : [],
        'sample_user' => $user ? $user->makeVisible(['user_id']) : null,
    ]);
});

// Test email config
Route::get('test/email-config', function () {
    return response()->json([
        'MAIL_MAILER' => env('MAIL_MAILER'),
        'MAIL_HOST' => env('MAIL_HOST'),
        'MAIL_PORT' => env('MAIL_PORT'),
        'MAIL_USERNAME' => env('MAIL_USERNAME') ? 'Set' : 'Not set',
        'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? 'Set' : 'Not set',
        'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
        'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
    ]);
});

// Test send email
Route::get('test/send-email', function () {
    try {
        $otp = \App\Models\EmailVerification::generateOTP();
        \Illuminate\Support\Facades\Mail::to('test@example.com')->send(new \App\Mail\OtpMail($otp));
        return response()->json([
            'success' => true,
            'message' => 'Email sent successfully! Check your mail log or inbox.',
            'otp' => $otp,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to send email',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], 500);
    }
});

// Route Đăng xuất và Lấy thông tin người dùng (Cần Middleware Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Route Lấy thông tin người dùng hiện tại
    Route::get('user', [AuthController::class, 'showAuthenticatedUser']); // Frontend gọi /api/user
    // Route đăng xuất
    Route::post('logout', [AuthController::class, 'logout']);
});

// 🔴 SECURITY FIX: Moved to admin routes group below
// Route::apiResource('users', UserController::class);

// API Đặt vé & Lấy link thanh toán
Route::post('/booking/create', [BookingController::class, 'createBooking']);

// API Webhook để Momo gọi lại (IPN)
Route::post('/payment/momo-ipn', [BookingController::class, 'momoIpn']);

// --- ADMIN ROUTES (Protected by auth:sanctum + admin middleware) ---
Route::middleware(['auth:sanctum', \App\Http\Middleware\EnsureUserIsAdmin::class])->prefix('admin')->group(function () {

    // Dashboard Statistics
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index']);

    // User Management (CRUD)
    Route::apiResource('users', UserController::class);

    // Movie Management (CRUD)
    Route::apiResource('movies', App\Http\Controllers\Admin\MovieController::class);

    // Booking Management (View only)
    Route::get('/bookings', [BookingController::class, 'adminIndex']);
    Route::get('/bookings/{id}', [BookingController::class, 'adminShow']);
});
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/search', [MovieController::class, 'search']);
// Các route cũ của bạn (giữ nguyên)
Route::get('/cinema-corner/{section}', [CinemaCornerController::class, 'index']);
Route::get('/showtimes/{id}/seats', [SeatController::class, 'getSeatsByShowtime']);
Route::get('/showtimes', [ShowtimeController::class, 'getShowtimes']);
Route::post('/vnpay_payment', [VnpayController::class, 'createPayment']);