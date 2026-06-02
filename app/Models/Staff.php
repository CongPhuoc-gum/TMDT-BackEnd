<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Staff extends Model
{
    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    public $incrementing = false; // Tắt tự tăng để đồng nhất 1:1 với user_id

    protected $fillable = [
        'staff_id',
        'position',
        'department',
        'salary',
        'hire_date',
        'specializations',
        'availability',
    ];

    protected function casts(): array
    {
        return [
            'specializations' => 'array', // Tự động ép kiểu mảng cho các kỹ năng riêng
            'salary'          => 'decimal:2',
            'availability'    => 'boolean',
            'hire_date'       => 'date',
        ];
    }

    /**
     * Liên kết ngược về bảng User độc bản
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id', 'user_id');
    }
}
