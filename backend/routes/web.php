<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http; // <-- Quan trọng: Phải có dòng này mới gọi được Google

// Route mặc định trang chủ (Giữ nguyên)
Route::get('/', function () {
    return view('welcome');
});

// --- ROUTE TEST KẾT NỐI (Thêm mới) ---
Route::get('/test-connect', function () {
    try {
        // Thử gọi đến Google xem có bị chặn SSL không
        $response = Http::get('https://www.google.com');

        // Nếu chạy đến đây nghĩa là thành công
        return "✅ KẾT NỐI THÀNH CÔNG! Mã lỗi cURL 60 đã được sửa. Bạn có thể đăng nhập Google được rồi.";
    } catch (\Exception $e) {
        // Nếu lỗi nó sẽ hiện ra đây
        return "❌ VẪN LỖI: " . $e->getMessage();
    }
});
