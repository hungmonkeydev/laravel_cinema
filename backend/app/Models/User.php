<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password', // THÊM DÒNG NÀY: Để nhận dữ liệu từ Controller
        'password_hash',
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
        // Không cần email_verified_at ở đây vì DB không có
        'password_hash' => 'hashed',
    ];

    /**
     * Chỉ định cho Laravel biết cột chứa mật khẩu đã mã hóa là password_hash
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Mutator: Khi Controller gửi 'password', nó sẽ tự mã hóa và lưu vào 'password_hash'
     */
    public function setPasswordAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['password_hash'] = bcrypt($value);
        }
    }
}
