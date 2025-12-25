<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users'; // Đảm bảo trỏ đúng bảng
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password_hash', // Đổi từ password thành password_hash cho khớp DB
        'role',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_avatar_url',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected $casts = [
        // XÓA DÒNG email_verified_at Ở ĐÂY
        'password_hash' => 'hashed',
    ];

    // Chỉ định cho Laravel biết cột mật khẩu tên là gì
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Nếu bạn muốn dùng User::create(['password' => '...']) thì dùng hàm này
    // Nhưng hãy chắc chắn trong Controller bạn KHÔNG gửi 'email_verified_at' đi kèm
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password_hash'] = bcrypt($value);
        }
    }
}