<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',      // Ví dụ loại trừ tất cả các route bắt đầu bằng /api
        'payment/webhook' // Ví dụ loại trừ một route cụ thể
    ];
}
