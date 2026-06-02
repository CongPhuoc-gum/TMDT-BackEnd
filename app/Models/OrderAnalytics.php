<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAnalytics extends Model
{
    protected $table = 'order_analytics';
    protected $primaryKey = 'analytics_id';

    protected $fillable = [
        'date',
        'total_revenue',
        'total_orders',
        'bestselling_products',
    ];

    // Ép kiểu dữ liệu JSON sang mảng Array trong PHP tự động khi gọi
    protected $casts = [
        'bestselling_products' => 'array',
        'date' => 'date',
    ];
}
