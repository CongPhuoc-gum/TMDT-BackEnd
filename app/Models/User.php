<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'fullname',
        'email',
        'password_hash',
        'phone',
        'avatar_url',
        'otp_code',
        'otp_expires_at',
        'google_id',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'otp_expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Ép Laravel Auth dùng trường password_hash thay vì trường 'password' mặc định
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash ?? '';
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class, 'customer_id', 'user_id');
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class, 'staff_id', 'user_id');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'admin_id', 'user_id');
    }
}