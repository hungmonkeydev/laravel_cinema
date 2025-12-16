<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan; // <-- Thêm dòng này để dùng lệnh Artisan

// Route mặc định trang chủ
Route::get('/', function () {
    return view('welcome');
});

// Route test kết nối (Giữ nguyên cái cũ của bạn)
Route::get('/test-connect', function () {
    try {
        $response = Http::get('https://www.google.com');
        return "✅ KẾT NỐI THÀNH CÔNG! Mã lỗi cURL 60 đã được sửa.";
    } catch (\Exception $e) {
        return "❌ VẪN LỖI: " . $e->getMessage();
    }
});

// 👇 ROUTE MỚI: XÓA CACHE (Thêm đoạn này vào cuối) 👇
Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    return '<h1>✅ Đã xóa sạch Cache thành công! (Config, Route, View)</h1>';
});
