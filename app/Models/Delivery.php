<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $table = 'delivery';
    protected $primaryKey = 'delivery_id';
    protected $fillable = ['order_id', 'staff_id', 'estimated_date', 'actual_date', 'status', 'delivery_address', 'notes'];

    protected function casts(): array
    {
        return [
            'estimated_date' => 'date',
            'actual_date'    => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
