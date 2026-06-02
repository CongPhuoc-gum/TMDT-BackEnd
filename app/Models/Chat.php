<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    protected $table = 'chat';
    protected $primaryKey = 'chat_id';
    
    // Tắt timestamps mặc định của Laravel vì schema dùng conversation_start
    public $timestamps = false; 

    protected $fillable = [
        'customer_id',
        'staff_id',
        'status',
        'conversation_start'
    ];

    /**
     * Một phòng chat có nhiều tin nhắn
     */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'chat_id');
    }

    /**
     * Phòng chat thuộc về một khách hàng
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id', 'user_id');
    }

    /**
     * Phòng chat được phụ trách bởi một nhân viên
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id', 'user_id');
    }
}