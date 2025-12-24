<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str; // <--- QUAN TRỌNG: Phải có dòng này mới dùng được Str::random

class SocialAuthController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Tìm user theo Google ID trước
            $user = User::where('provider_id', $googleUser->getId())->first();

            // Nếu không thấy thì tìm theo Email
            if (!$user) {
                $user = User::where('email', $googleUser->getEmail())->first();
                
                if ($user) {
                    // Đã có user (đăng ký thường), giờ cập nhật thêm Google ID vào
                    $user->update([
                        'provider' => 'google',
                        'provider_id' => $googleUser->getId(),
                        'provider_avatar_url' => $googleUser->getAvatar(),
                    ]);
                } else {
                    // Chưa có ai -> Tạo mới hoàn toàn
                    $user = User::create([
                        'full_name' => $googleUser->getName(),
                        'email' => $googleUser->getEmail(),
                        'password' => bcrypt(Str::random(16)), // Random pass
                        'role' => 'customer',
                        'email_verified_at' => now(),
                        'provider' => 'google',
                        'provider_id' => $googleUser->getId(),
                        'provider_avatar_url' => $googleUser->getAvatar(),
                    ]);
                }
            }

            // Đăng nhập
            Auth::login($user);
            $token = $user->createToken('google-auth-token')->plainTextToken;

            // Lấy URL frontend từ biến môi trường (Mặc định Railway)
            $frontendUrl = env('FRONTEND_URL', 'https://frontend-laravel-production.up.railway.app');

            return redirect()->to("$frontendUrl/auth/callback?login=success&token=$token");

        } catch (\Throwable $e) {
            Log::error('Google Login Error: ' . $e->getMessage());
            $frontendUrl = env('FRONTEND_URL', 'https://frontend-laravel-production.up.railway.app');
            return redirect()->to("$frontendUrl/auth/callback?login=error&message=" . urlencode("Lỗi đăng nhập Google"));
        }
    }
}