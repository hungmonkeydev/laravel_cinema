<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Import để sử dụng Rule::in

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::latest()->get();
        return response()->json(
            [
                'success' => true,
                'data' => $users,
                'message' => 'Users retrieved successfully.'
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     * (Không dùng trong API thuần)
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validation BẮT BUỘC cho việc tạo người dùng mới
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed', // BẮT BUỘC có 'password_confirmation'
            'phone' => 'nullable|string|max:20',
            // Chỉ cho phép các vai trò hợp lệ
            'role' => ['nullable', Rule::in(['admin', 'customer'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422); // 422 Unprocessable Entity
        }

        $validatedData = $validator->validated();

        try {
            // 2. Tạo người dùng mới
            $user = User::create([
                'full_name' => $validatedData['full_name'],
                'email' => $validatedData['email'],

                // Gửi trường `password` để Setter `setPasswordAttribute` ở Model
                // sẽ băm và lưu vào `password_hash` tự động.
                'password' => $validatedData['password'],

                'phone' => $validatedData['phone'] ?? null,
                'role' => $validatedData['role'] ?? 'customer',
                'is_active' => 1,
            ]);

            // 3. Trả về phản hồi thành công
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully.',
                'data' => $user->makeHidden(['password_hash', 'remember_token']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error: Could not create user.',
                'error_details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Thay thế find($id) bằng findOrFail để đảm bảo lỗi 404
        $user = User::where('user_id', $id)->first();

        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User not found.'
                ],
                404
            );
        }
        return response()->json(
            [
                'success' => true,
                'data' => $user,
                'message' => 'User retrieved successfully.'
            ]
        );
    }


    public function edit(string $id)
    {
        // Hàm này không cần thiết cho API thuần
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // 1. Validation (Xác thực dữ liệu)
        // Lưu ý: Đã loại bỏ 'confirmed' khỏi password
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id . ',user_id',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(['admin', 'customer'])], // Sử dụng Rule::in cho rõ ràng
            // Bổ sung thêm validation cho 'password_confirmation' để tự kiểm tra
            'password' => 'nullable|string|min:8',
        ]);

        // 2. Tìm kiếm người dùng (Sử dụng primaryKey là 'user_id')
        $user = User::where('user_id', $id)->first();

        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại.'], 404);
        }

        // 3. Chuẩn bị dữ liệu để cập nhật
        // Sử dụng $request->only() để chỉ lấy các trường cần cập nhật, 
        // khắc phục lỗi "Undefined array key 'phone'"
        $updateData = $request->only(['full_name', 'email', 'phone', 'role']);

        // Xử lý mật khẩu (Chỉ cập nhật nếu người dùng cung cấp mật khẩu mới)
        if ($request->filled('password')) {
            // Gán vào trường `password` để Setter ở Model xử lý hash.
            $updateData['password'] = $request->input('password');
        }

        // 4. Cập nhật bản ghi
        $user->update($updateData);

        // 5. Trả về phản hồi
        return response()->json([
            'message' => 'Cập nhật người dùng thành công!',
            'user' => $user->makeHidden(['password_hash', 'remember_token'])
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::where('user_id', $id)->first(); // Thay đổi để tìm kiếm bằng user_id

        if (!$user) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'User not found.'
                ],
                404
            );
        }
        $user->delete();
        return response()->json(
            [
                'success' => true,
                'message' => 'User deleted successfully.'
            ]
        );
    }
}
