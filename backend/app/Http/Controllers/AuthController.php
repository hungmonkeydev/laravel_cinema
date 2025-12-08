<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        // dd($request->all());
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

        // Tạo User
        $user = User::create([
            'full_name' => $request->full_name, 
            'email' => $request->email,
            'phone' => $request->phone ?? null,
            'password' => $request->password,
            'role' => 'customer',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công! Vui lòng đăng nhập.',
            'data' => $user->makeHidden(['password_hash', 'remember_token']),
        ], 201);
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

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'data' => $user->makeHidden(['password_hash', 'remember_token']),
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
}
