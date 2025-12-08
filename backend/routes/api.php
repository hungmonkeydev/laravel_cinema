<?php

// Import các lớp cần thiết cho việc định nghĩa route và Controller.
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

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

// Route Đăng xuất và Lấy thông tin người dùng (Cần Middleware Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    // Route Lấy thông tin người dùng hiện tại
    Route::get('user', [AuthController::class, 'showAuthenticatedUser']); // Frontend gọi /api/user
    // Route đăng xuất
    Route::post('logout', [AuthController::class, 'logout']);
});

// Định nghĩa tất cả các route chuẩn cho tài nguyên 'users' (người dùng).
Route::apiResource('users', UserController::class);
// API Đặt vé & Lấy link thanh toán
Route::post('/booking/create', [BookingController::class, 'createBooking']);

// API Webhook để Momo gọi lại (IPN)
Route::post('/payment/momo-ipn', [BookingController::class, 'momoIpn']);

// LƯU Ý: Nếu bạn dùng Route::apiResource('users', ...) cho ĐĂNG KÝ (store), 
// bạn nên xóa hàm register ở trên và chỉ cần gọi POST /api/users.
// Tuy nhiên, việc tạo route register/login riêng biệt là cách làm chuẩn hơn.