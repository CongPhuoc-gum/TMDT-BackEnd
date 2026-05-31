<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    // Đã sửa: Trỏ chính xác vào bảng số ít trong database của bạn
    protected $table = 'customer';
    
    // Đã sửa: Khai báo chính xác khóa chính
    protected $primaryKey = 'customer_id';
    
    // Đã sửa: Tắt tự động tăng vì giá trị này đồng bộ theo user_id
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'address',
        'city',
        'postal_code',
        'country',
        'loyalty_points',
        'total_spent',
        'preferred_delivery_time',
    ];

    protected function casts(): array
    {
        return [
            'loyalty_points' => 'integer',
            'total_spent' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'user_id');
    }
}