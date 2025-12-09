<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash; // Cần thiết cho Setter

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Primary key is 'user_id' (custom)
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     * Chỉ định các cột trong DB trừ mật khẩu (vì dùng Setter).
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        // 'password_hash' sẽ được gán giá trị thông qua Setter
        'password',
        'phone',
        'role',
        'provider',
        'provider_id',
        'provider_token',
        'provider_refresh_token',
        'provider_avatar_url',
    ];

    /**
     * BẮT BUỘC: PHƯƠNG THỨC SETTER (Mutator)
     * Khi gọi User::create(['password' => '123456']), setter này sẽ Hash mật khẩu và gán cho cột DB.
     * Cho phép null để support OAuth users không có password.
     */
    public function setPasswordAttribute($value)
    {
        if ($value !== null) {
            $this->attributes['password_hash'] = Hash::make($value);
        }
    }

    // PHƯƠNG THỨC ÁNH XẠ: Trả về mật khẩu hash cho Auth::attempt()
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * The attributes that should be hidden for serialization.
     * ...
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
        'provider_token',
        'provider_refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     * Loại bỏ 'password_hash' => 'hashed' vì chúng ta dùng Setter Hash thủ công.
     * ...
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // KHÔNG CẦN 'password_hash' => 'hashed', vì Setter đã Hash
        ];
    }
    protected $casts = [
        'email_verified_at' => 'datetime',
       // KHÔNG CẦN 'password_hash' => 'hashed', vì Setter đã Hash
    ];
}
