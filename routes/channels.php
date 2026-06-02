<?php

use Illuminate\Support\Facades\Broadcast;

// Kênh riêng của từng phòng chat: Chỉ cho phép chính Customer của phòng đó hoặc Nhân viên (Staff) vào nghe
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = \App\Models\Chat::find($chatId);
    if (!$chat) return false;

    return $user->user_id === $chat->customer_id || $user->role === 'staff' || $user->role === 'admin';
});

// Kênh tổng của nhân viên chăm sóc khách hàng: Chỉ cho tài khoản có Role Staff/Admin vào
Broadcast::channel('support.staff', function ($user) {
    return $user->role === 'staff' || $user->role === 'admin';
});
