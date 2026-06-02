<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    // Khai báo chính xác tên bảng lưu ảnh sản phẩm dưới database
    protected $table = 'product_image';
    protected $primaryKey = 'image_id';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'image_url',
    ];

    /**
     * Mối quan hệ ngược: Bức ảnh này là của sản phẩm nào
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}