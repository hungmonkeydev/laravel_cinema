<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     * ...
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class, // ✨ Giữ nguyên (CORS global)
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * The application's route middleware groups.
     * ...
     */
    protected $middlewareGroups = [
        'api' => [
            // KHẮC PHỤC LỖI 500: ĐẢO NGƯỢC THỨ TỰ CHO SANCTUM 
            
            // 1. Giải mã Cookie trước tiên (BẮT BUỘC để đọc Session ID đã mã hóa)
            \App\Http\Middleware\EncryptCookies::class, 
            
            // 2. Xử lý Cookie (Đọc Session ID đã giải mã)
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, 
            
            // 3. Khởi tạo Session (Sử dụng Session ID từ Cookie để tải Session Store)
            \Illuminate\Session\Middleware\StartSession::class, 

            // 4. Sanctum kiểm tra trạng thái (Phải chạy SAU khi Session đã được tải)
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        
        'web' => [
            // Cấu hình nhóm 'web' của bạn đã đúng chuẩn
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     * ...
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    ];
}