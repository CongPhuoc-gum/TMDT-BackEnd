<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingAddress extends Model
{
    use HasFactory, SoftDeletes;

    // Khai báo chính xác tên bảng dưới database của bạn
    protected $table = 'shipping_addresses';
    
    // Khai báo khóa chính của bảng
    protected $primaryKey = 'address_id';

    // Các trường cho phép điền dữ liệu qua API
    protected $fillable = [
        'customer_id',
        'receiver_name',
        'receiver_phone',
        'address',
        'city',
        'is_default',
    ];

    // Ép kiểu trường is_default về true/false cho Front-end dễ xử lý
    protected $casts = [
        'is_default' => 'boolean',
    ];
}