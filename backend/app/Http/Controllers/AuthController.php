<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\GoogleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthController extends Controller
{
    /**
     * Xử lý chức năng Đăng ký (Register)
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực dữ liệu.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // SỬA LỖI 1: Bỏ bcrypt(), gửi password thô để Model tự mã hóa
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password, // <-- Quan trọng: Gửi raw text, Model sẽ tự Hash
                'role' => 'customer',
                // 'email_verified_at' => now(), 
            ]);

            // Tự động đăng nhập
            Auth::login($user);
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công! Bạn đã được đăng nhập.',
                'data' => [
                    // Ẩn password_hash thay vì password
                    'user' => $user->makeHidden(['password_hash', 'remember_token']),
                    'role' => $user->role,
                    'redirect_to' => '/', 
                    'access_token' => $token, 
                ],
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Register error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xử lý chức năng Đăng nhập (Login)
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        // SỬA LỖI 2: Check pass với cột 'password_hash'
        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng',
            ], 401);
        }

        Auth::login($user);
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => [
                'user' => $user->makeHidden(['password_hash', 'remember_token']),
                'role' => $user->role,
                'redirect_to' => $user->role === 'admin' ? '/admin' : '/',
                'access_token' => $token,
            ],
        ], 200);
    }

    // ... GIỮ NGUYÊN CÁC HÀM KHÁC (showAuthenticatedUser, logout) ...
    public function showAuthenticatedUser(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'message' => 'Đăng xuất thành công.'], 200);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        $defaultFrontendUrl = 'https://frontend-laravel-production.up.railway.app';
        $frontendUrl = env('FRONTEND_URL', $defaultFrontendUrl);

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // SỬA LỖI 3: Bỏ Hash::make, dùng Str::random thôi
                $user = User::create([
                    'full_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Str::random(16), // Model tự mã hóa
                    'role' => 'customer',
                    // 'email_verified_at' => now(),
                ]);
            }

            Auth::login($user);
            $token = $user->createToken('google-auth-token')->plainTextToken;

            return redirect()->to("$frontendUrl/auth/callback?login=success&token=$token");
        } catch (Throwable $e) {
            return redirect()->to("$frontendUrl/auth/callback?login=error&message=" . urlencode($e->getMessage()));
        }
    }
}