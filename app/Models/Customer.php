<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
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