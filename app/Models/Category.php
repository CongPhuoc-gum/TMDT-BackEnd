<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Khai báo chính xác tên bảng danh mục dưới database của bạn
    protected $table = 'category';
    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'description',
    ];

    /**
     * Một danh mục (ví dụ: Hoa sinh nhật) thì có NHIỀU sản phẩm hoa tươi
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }
}