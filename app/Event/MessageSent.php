<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ChatMessage $message)
    {
        // Tải kèm thông tin người gửi để hiển thị tên/avatar trên giao diện chat realtime
        $this->message = $message->load('sender:user_id,fullname,avatar_url');
    }

    /**
     * Kênh phát tín hiệu: Đặt kênh riêng tư cho từng phòng chat cụ thể
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->chat_id),
        ];
    }

    /**
     * Tên sự kiện phía Frontend lắng nghe
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}