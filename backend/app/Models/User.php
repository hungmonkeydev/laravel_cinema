<?php

namespace App\Models;

// Các thư viện cần thiết
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 1. Khai báo Khóa chính
     * Vì bảng của bạn dùng 'user_id' chứ không phải 'id'
     */
    protected $primaryKey = 'user_id';

    /**
     * 2. Các trường được phép gán dữ liệu (Mass Assignment)
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'phone',
        'role',
        'email_verified_at',
        // --- THÊM CÁC DÒNG NÀY ---
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_avatar_url',
    ];

    /**
     * Các trường cần ẩn đi khi trả về API
     */
    protected $hidden = [
        'password_hash',   // Ẩn mật khẩu đã mã hóa
        'remember_token',
    ];

    /**
     * Các trường ép kiểu dữ liệu
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_hash' => 'hashed', // Hỗ trợ bảo mật Laravel 10+
    ];

    /* =========================================================
       PHẦN QUAN TRỌNG ĐỂ SỬA LỖI CỦA BẠN
       ========================================================= */

    /**
     * 3. Chỉ định tên cột mật khẩu cho Laravel Auth biết
     * Mặc định Laravel tìm cột 'password', ta trỏ nó về 'password_hash'
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * 4. Mutator: Tự động mã hóa mật khẩu khi tạo mới/cập nhật
     * Khi Controller gọi: User::create(['password' => '123456'])
     * Hàm này sẽ tự chạy, mã hóa '123456' và lưu vào cột 'password_hash'
     */
    public function setPasswordAttribute($value)
    {
        // Chỉ mã hóa nếu mật khẩu không rỗng
        if (!empty($value)) {
            $this->attributes['password_hash'] = bcrypt($value);
        }
    }
}
