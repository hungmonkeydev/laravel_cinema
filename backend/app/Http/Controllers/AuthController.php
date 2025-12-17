<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite; // Thư viện Socialite
use GuzzleHttp\Client; // Để tắt kiểm tra SSL (nếu cần dùng trong controller)
use Illuminate\Support\Str; // Để tạo mật khẩu ngẫu nhiên

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
        // TẠM THỜI BỎ QUA GỬI EMAIL - TẠO USER TRỰC TIẾP
        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone ?? null,
            'password' => $request->password,
            'role' => 'customer',
            'email_verified_at' => now(), // Tạm thời verify luôn
        ]);

        Auth::login($user);
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công!',
            'data' => [
                'user' => $user->makeHidden(['password_hash', 'remember_token']),
                'role' => $user->role,
                'redirect_to' => '/',
                'access_token' => $token,
            ],
        ], 200);

        /* CODE CŨ - TẠM COMMENT ĐỂ TEST
        EmailVerification::createAndSend($request->email, [
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mã OTP đã được gửi đến email của bạn.',
            'data' => [
                'email' => $request->email,
                'requires_otp' => true,
            ],
        ], 200);
        */
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Không thể tạo tài khoản. Vui lòng thử lại.',
            'error' => $e->getMessage(),
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

    /**
     * Lấy thông tin người dùng đã xác thực
     */
    public function showAuthenticatedUser(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Xử lý chức năng Đăng xuất (Logout)
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
     * Verify OTP
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

        $verification = EmailVerification::verify($request->email, $request->otp);

        if (!$verification) {
            return response()->json([
                'success' => false,
                'message' => 'Mã OTP không đúng hoặc đã hết hạn.',
            ], 400);
        }

        $pendingData = $verification->getRegistrationData();

        if (!$pendingData) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin đăng ký. Vui lòng đăng ký lại.',
            ], 400);
        }

        $user = User::create([
            'full_name' => $pendingData['full_name'],
            'email' => $pendingData['email'],
            'phone' => $pendingData['phone'] ?? null,
            'password' => $pendingData['password'],
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        Auth::login($user);

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

    // --- PHẦN GOOGLE LOGIN (ĐÃ SỬA LỖI) ---

    /**
     * Bắt đầu đăng nhập Google (Thêm hàm này nếu chưa có)
     */
    public function redirectToGoogle()
    {
        // Nếu bạn đã cấu hình 'guzzle' => ['verify' => false] trong config/services.php
        // thì không cần dòng setHttpClient ở đây nữa.
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    /**
     * Xử lý Callback từ Google
     */
    public function handleGoogleCallback()
    {
        try {
            // Lấy thông tin user từ Google
            // Lưu ý: Nếu chưa config tắt SSL trong services.php thì dùng:
            // ->setHttpClient(new Client(['verify' => false]))->stateless()->user();
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Tìm user trong DB bằng email
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Nếu chưa có thì tạo mới
                $user = User::create([
                    'full_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => bcrypt(Str::random(16)), // Mật khẩu ngẫu nhiên
                    'role' => 'customer',
                    'email_verified_at' => now(), // Google đã xác thực
                ]);
            }

            // Đăng nhập user
            Auth::login($user);

            // Tạo token Sanctum
            $token = $user->createToken('google-auth-token')->plainTextToken;

            // Chuyển hướng về Frontend (React) kèm theo Token
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            return redirect()->to("$frontendUrl/auth/callback?login=success&token=$token");
        } catch (\Exception $e) {
            // Nếu lỗi thì chuyển về Frontend kèm thông báo lỗi
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            return redirect()->to("$frontendUrl/auth/callback?login=error&message=" . urlencode($e->getMessage()));
        }
    }
}
