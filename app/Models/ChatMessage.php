<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $table = 'chat_message';
    protected $primaryKey = 'message_id';
    
    // Tắt timestamps mặc định của Laravel vì schema dùng sent_at
    public $timestamps = false; 

    protected $fillable = [
        'chat_id',
        'sender_id',
        'message_text',
        'is_read',
        'sent_at'
    ];

    /**
     * Tin nhắn thuộc về một phòng chat
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    /**
     * Người gửi tin nhắn (có thể là Customer hoặc Staff, đều trỏ về bảng user)
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id', 'user_id');
    }
}