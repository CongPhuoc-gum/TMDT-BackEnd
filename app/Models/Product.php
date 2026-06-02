<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Khai báo chính xác tên bảng sản phẩm hoa dưới database
    protected $table = 'product';
    protected $primaryKey = 'product_id';

   protected $fillable = [
        'category_id',
        'flower_meaning_id',
        'product_name',
        'description',
        'price',
        'discount_price',
        'stock_quantity',
        'size',
        'availability',
        'rating',
        'review_count',
    ];

    /**
     * Mối quan hệ: Một sản phẩm hoa tươi có NHIỀU hình ảnh đi kèm
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id');
    }

    /**
     * Mối quan hệ ngược: Một sản phẩm phải thuộc về một Danh mục nào đó
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
}