<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // 🌟 1. Ép Laravel nhận đúng tên bảng `order` dạng số ít trong SQL của bạn
    protected $table = 'order'; 

    // 🌟 2. Khai báo khóa chính của bảng
    protected $primaryKey = 'order_id';

    // 🌟 3. Cho phép gán các cột dữ liệu này khi dùng hàm Order::create
    protected $fillable = [
        'customer_id',
        'coupon_id',
        'order_date',
        'delivery_date',
        'total_price',
        'discount_amount',
        'distance_km',
        'shipping_fee',
        'final_price',
        'status',
        'delivery_address',
        'special_instructions',
        'payment_method',
        'paid'
    ];

    // Vô hiệu hóa timestamp mặc định nếu bảng của bạn đã dùng `created_at` tự động của MySQL
    public $timestamps = false;
}