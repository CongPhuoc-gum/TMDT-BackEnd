<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $table = 'coupon';
    protected $primaryKey = 'coupon_id';
    public $timestamps = true; 

    protected $fillable = [
        'coupon_code',
        'admin_id',
        'discount_type',
        'discount_value',
        'min_purchase_amount',
        'max_discount_amount',
        'usage_limit',
        'current_usage',
        'applicable_categories',
        'start_date',
        'end_date',
        'is_active',
    ];
}