<?php

namespace App\Http\Controllers;

use App\Models\User;
// use App\Models\EmailVerification; // Đã comment: Không dùng nữa
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
     * Xử lý chức năng Đăng ký (Register) - KHÔNG CẦN OTP
     */
    public function register(Request $request)
    {
        // 1. Validate dữ liệu đầu vào (Giữ nguyên)
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
            // 2. THAY ĐỔI LỚN Ở ĐÂY:
            // Không gọi EmailVerification nữa, mà tạo User luôn!
            
            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                // Quan trọng: Phải mã hóa mật khẩu ngay tại đây
                'password' => bcrypt($request->password), 
                'role' => 'customer',
                // Quan trọng: Đánh dấu là đã xác thực email luôn
                'email_verified_at' => now(), 
            ]);

            // 3. Tự động đăng nhập luôn sau khi đăng ký
            Auth::login($user);

            // 4. Tạo Token để trả về cho Frontend dùng luôn
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công! Bạn đã được đăng nhập.',
                'data' => [
                    'user' => $user->makeHidden(['password_hash', 'remember_token']),
                    'role' => $user->role,
                    'redirect_to' => '/', // Frontend sẽ chuyển hướng về trang chủ
                    'access_token' => $token, // Frontend lưu token này để dùng
                ],
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Register error: ' . $e->getMessage());
            
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

        // Tìm user (Sửa logic check password một chút cho chuẩn Laravel)
        $user = User::where('email', $credentials['email'])->first();

        // Kiểm tra User tồn tại và Khớp mật khẩu
        // Lưu ý: Cột password trong DB của bạn tên là 'password' hay 'password_hash'?
        // Nếu là 'password' (chuẩn Laravel) thì dùng $user->password.
        // Nếu bạn đặt tên cột là 'password_hash' thì dùng $user->password_hash.
        // Dưới đây mình để mặc định là check với $user->password (chuẩn).
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
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
                'user' => $user->makeHidden(['password', 'remember_token']),
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

        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công.'
        ], 200);
    }

    /*
    // --- ĐÃ COMMENT: CÁC HÀM LIÊN QUAN ĐẾN OTP KHÔNG DÙNG NỮA ---

    public function verifyOtp(Request $request)
    {
        // ... Code cũ ...
    }

    public function resendOtp(Request $request)
    {
        // ... Code cũ ...
    }
    
    */

    // --- PHẦN GOOGLE LOGIN ---

    /**
     * Bắt đầu đăng nhập Google
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    /**
     * Xử lý Callback từ Google
     */
    public function handleGoogleCallback()
    {
        $defaultFrontendUrl = 'https://frontend-laravel-production.up.railway.app';
        $frontendUrl = env('FRONTEND_URL', $defaultFrontendUrl);

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'full_name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'password' => Hash::make(Str::random(16)), // Sửa thành Hash::make cho chuẩn
                    'role' => 'customer',
                    'email_verified_at' => now(),
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
