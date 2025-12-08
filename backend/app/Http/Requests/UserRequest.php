<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền thực hiện yêu cầu này hay không.
     * Thường là TRUE cho API đăng ký/tạo user.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Lấy các quy tắc xác thực áp dụng cho yêu cầu.
     * Chúng ta sử dụng tên trường từ JSON INPUT (full_name, email, password)
     */
    public function rules(): array
    {
        return [
            // DB dùng full_name thay vì name
            'full_name' => 'required|string|max:100',

            // unique:users,email - Kiểm tra cột 'email' trong bảng 'users'
            'email' => 'required|string|email|unique:users,email|max:100',

            // Quy tắc cho trường 'password' (mặc dù DB là password_hash, validation dùng tên trường input)
            'password' => 'required|string|min:8',

            //Có thể thêm xác thực cho phone và role nếu cần
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,customer',
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi xác thực.
     */
    public function messages()
    {
        return [
            'full_name.required' => 'Họ và tên bắt buộc phải nhập.',
            'full_name.max' => 'Họ và tên không được vượt quá :max ký tự.',

            'email.required' => 'Email bắt buộc phải nhập.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',

            'password.required' => 'Mật khẩu bắt buộc phải nhập.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
        ];
    }

    /**
     * Tùy chỉnh tên thuộc tính (attribute names) để hiển thị trong thông báo.
     */
    public function attributes()
    {
        return [
            'full_name' => 'Họ và tên',
            'email' => 'Địa chỉ Email',
            'password' => 'Mật khẩu',
        ];
    }
}
