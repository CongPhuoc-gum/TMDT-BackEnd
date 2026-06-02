<?php

namespace App\Events;

use App\Models\Chat;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    /**
     * Kênh công khai hoặc riêng tư cho nhân viên lắng nghe có phòng chat mới kích hoạt
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('support.staff')
        ];
    }

    public function broadcastAs(): string
    {
        return 'chat.started';
    }
}
