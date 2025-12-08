<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // Đảm bảo dòng này có để chạy API
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Cấu hình ngoại lệ CSRF tại đây
        // Lưu ý: Nếu route api của bạn có tiền tố 'api/', hãy thêm vào đây
        $middleware->validateCsrfTokens(except: [
            'payment/momo-ipn',      // Nếu link là http://domain/payment/momo-ipn
            'api/payment/momo-ipn',  // Nếu link là http://domain/api/payment/momo-ipn (Thường dùng cái này)
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create(); // Hàm create() phải nằm ở cuối cùng