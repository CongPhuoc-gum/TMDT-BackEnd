<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admin extends Model
{
    // Đã sửa: Trỏ chính xác vào bảng số ít 'admin'
    protected $table = 'admin';
    
    protected $primaryKey = 'admin_id';
    public $incrementing = false;

    protected $fillable = [
        'admin_id',
        'role',
        'permissions',
        'last_login',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'last_login' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id', 'user_id');
    }
}