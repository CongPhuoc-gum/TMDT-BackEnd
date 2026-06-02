<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlowerMeaning extends Model
{
    use HasFactory;

    // Khớp chính xác tên bảng số ít trong database của nhóm bạn
    protected $table = 'flower_meaning';

    // Khai báo chính xác tên cột Khóa Chính tự động tăng
    protected $primaryKey = 'flower_meaning_id';

    // Cho phép đổ dữ liệu hàng loạt vào các cột thực tế
    protected $fillable = [
        'flower_name',
        'meaning',
        'symbolism',
        'emotion',
        'color_associations',
        'cultural_significance',
        'care_instructions',
        'is_available'
    ];

    /**
     * Định hình kiểu dữ liệu tự động khi lấy dữ liệu ra khỏi DB
     */
    protected function casts(): array
    {
        return [
            'color_associations' => 'array', // Ép kiểu JSON từ DB thành Array trong PHP mượt mà
            'is_available'       => 'boolean',
            'created_at'         => 'datetime',
            'updated_at'         => 'datetime',
        ];
    }
}
