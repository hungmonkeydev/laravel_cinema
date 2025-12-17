<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

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
     * Create and send OTP to email with registration data
     */
    public static function createAndSend(string $email, ?array $registrationData = null): self
    {
        // Delete old OTPs for this email
        self::where('email', $email)
            ->whereNull('verified_at')
            ->delete();

        // Generate new OTP
        $otp = self::generateOTP();

        // Create verification record (expires in 10 minutes)
        $verification = self::create([
            'email' => $email,
            'otp' => $otp,
            'registration_data' => $registrationData ? json_encode($registrationData) : null,
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send email
        Mail::to($email)->send(new OtpMail($otp));

        return $verification;
    }

    /**
     * Verify OTP and return verification record
     */
    public static function verify(string $email, string $otp): ?self
    {
        $verification = self::where('email', $email)
            ->where('otp', $otp)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$verification) {
            return null;
        }

        $verification->update(['verified_at' => now()]);
        return $verification;
    }

    /**
     * Get registration data
     */
    public function getRegistrationData(): ?array
    {
        return $this->registration_data ? json_decode($this->registration_data, true) : null;
    }

    /**
     * Check if email is verified
     */
    public static function isVerified(string $email): bool
    {
        return self::where('email', $email)
            ->whereNotNull('verified_at')
            ->exists();
    }
}
