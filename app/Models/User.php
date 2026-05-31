<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <--- THÊM DÒNG NÀY (Đường dẫn chuẩn cho Laravel mới)

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // <--- THÊM HasApiTokens VÀO ĐÂY

    // Chỉ định chính xác tên bảng trong Database của bạn
    protected $table = 'user';

    // Khai báo lại tên cột khóa chính của bảng
    protected $primaryKey = 'user_id';

    /**
     * Các trường dữ liệu được phép gán giá trị hàng loạt (Mass Assignment)
     */
    protected $fillable = [
        'fullname',
        'email',
        'password_hash',
        'phone',
        'avatar_url',
        'google_id',
        'is_active',
    ];

    /**
     * Ẩn các trường thông tin nhạy cảm khi trả dữ liệu JSON API về cho Client
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Ép kiểu dữ liệu (Casting) các trường cần thiết
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Ghi đè phương thức này để Laravel Auth hiểu cột mật khẩu của bạn tên là password_hash
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Mối quan hệ 1-1 với bảng Admin
     */
    public function admin()
    {
        return $this->hasOne(Admin::class, 'admin_id', 'user_id');
    }

    /**
     * Mối quan hệ 1-1 với bảng Staff
     */
    public function staff()
    {
        return $this->hasOne(Staff::class, 'staff_id', 'user_id');
    }

    /**
     * Mối quan hệ 1-1 với bảng Customer
     */
    public function customer()
    {
        return $this->hasOne(Customer::class, 'customer_id', 'user_id');
    }
}