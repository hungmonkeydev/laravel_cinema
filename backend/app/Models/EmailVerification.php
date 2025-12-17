<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\OtpMail; // Bạn cần đảm bảo file này tồn tại (xem mục 2 bên dưới)
use Throwable;

class EmailVerification extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'registration_data',
        'expires_at',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Generate a random 6-digit OTP
     */
    public static function generateOTP(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create and send OTP to email
     */
    public static function createAndSend(string $email, ?array $registrationData = null): self
    {
        // 1. Xóa OTP cũ chưa verify của email này
        self::where('email', $email)
            ->whereNull('verified_at')
            ->delete();

        // 2. Tạo OTP mới
        $otp = self::generateOTP();

        // 3. Lưu vào Database
        $verification = self::create([
            'email' => $email,
            'otp' => $otp,
            'registration_data' => $registrationData ? json_encode($registrationData) : null,
            'expires_at' => now()->addMinutes(10), // Hết hạn sau 10 phút
        ]);

        // 4. Gửi Email (ĐÃ COMMENT LẠI ĐỂ KHÔNG GỬI NỮA)
        // try {
        //    Mail::to($email)->send(new OtpMail($otp));
        // } catch (Throwable $e) {
        //    Log::error("Failed to send OTP to {$email}: " . $e->getMessage());
        // }

        // --- THÊM DÒNG NÀY ĐỂ XEM OTP MÀ KHÔNG CẦN MAIL ---
        Log::info("DEBUG OTP cho email {$email} là: {$otp}");
        // --------------------------------------------------

        return $verification;
    }

    /**
     * Verify OTP
     */
    public static function verify(string $email, string $otp): ?self
    {
        $verification = self::where('email', $email)
            ->where('otp', $otp)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($verification) {
            $verification->update(['verified_at' => now()]);
        }

        return $verification;
    }
}
