<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    // 🌟 Ép Laravel nhận đúng bảng `order_item` trong SQL
    protected $table = 'order_item'; 

    protected $primaryKey = 'order_item_id';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal'
    ];

    public $timestamps = false;
}