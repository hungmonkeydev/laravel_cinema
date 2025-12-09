<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Xử lý chức năng Đăng ký (Register)
     * POST /api/register
     */
    public function register(Request $request)
    {
        // SỬA ĐỔI: Validation sử dụng 'full_name'
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

        // Tạo và gửi OTP với registration data
        try {
            EmailVerification::createAndSend($request->email, [
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => $request->password,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra và xác thực.',
                'data' => [
                    'email' => $request->email,
                    'requires_otp' => true,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi email. Vui lòng thử lại sau.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xử lý chức năng Đăng nhập (Login)
     * POST /api/login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không đúng',
            ], 401);
        }

        Auth::login($user);

        // Create Sanctum token for API authentication
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
    /**
     * Lấy thông tin người dùng đã xác thực
     * GET /api/user
     */
    public function showAuthenticatedUser(Request $request)
    {
        // Auth::user() được cung cấp bởi middleware 'auth:sanctum'
        return response()->json($request->user());
    }

    /**
     * Xử lý chức năng Đăng xuất (Logout)
     * POST /api/logout
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công.'
        ], 200);
    }

    /**
     * Verify OTP and complete registration
     * POST /api/verify-otp
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify OTP
        $verification = EmailVerification::verify($request->email, $request->otp);

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'Mã OTP không đúng hoặc đã hết hạn.',
            ], 400);
        }

        // Get registration data from verification record
        $pendingData = $verification->getRegistrationData();

        if (!$pendingData) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin đăng ký. Vui lòng đăng ký lại.',
            ], 400);
        }

        // Create user
        $user = User::create([
            'full_name' => $pendingData['full_name'],
            'email' => $pendingData['email'],
            'phone' => $pendingData['phone'] ?? null,
            'password' => $pendingData['password'],
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        // Auto login
        Auth::login($user);

        // Create Sanctum token for API authentication
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Xác thực thành công! Tài khoản đã được kích hoạt.',
            'data' => [
                'user' => $user->makeHidden(['password_hash', 'remember_token']),
                'role' => $user->role,
                'redirect_to' => $user->role === 'admin' ? '/admin' : '/',
                'access_token' => $token,
            ],
        ], 200);
    }

    /**
     * Resend OTP
     * POST /api/resend-otp
     */
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Email không hợp lệ.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            EmailVerification::createAndSend($request->email);

            return response()->json([
                'success' => true,
                'message' => 'Mã OTP mới đã được gửi đến email của bạn.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi email. Vui lòng thử lại sau.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
