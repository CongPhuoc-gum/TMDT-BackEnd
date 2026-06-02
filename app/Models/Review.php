<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'review';
    protected $primaryKey = 'review_id';
    public $timestamps = false; // Chỉ có trường created_at mặc định trong DB
    protected $fillable = ['product_id', 'customer_id', 'order_id', 'rating', 'comment'];

    protected function casts(): array
    {
        return ['rating' => 'integer'];
    }
}
