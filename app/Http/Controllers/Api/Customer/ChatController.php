<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    /**
     * Khách hàng khởi tạo hoặc lấy phòng chat đang hoạt động
     */
    public function initChat(Request $request)
    {
        $customerId = auth()->id(); // Lấy ID của Customer đang đăng nhập

        // Tìm phòng chat đang Active của khách hàng này
        $chat = Chat::where('customer_id', $customerId)
            ->where('status', 'Active')
            ->first();

        // Nếu chưa có phòng chat nào đang mở -> Tạo mới hoàn toàn
        if (!$chat) {
            $chat = Chat::create([
                'customer_id' => $customerId,
                'status'      => 'Active',
                'conversation_start' => now()
            ]);
        }

        // Lấy lịch sử tin nhắn của phòng chat này kèm thông tin người gửi
        $messages = ChatMessage::where('chat_id', $chat->chat_id)
            ->with('sender:user_id,fullname,avatar_url')
            ->orderBy('sent_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Kết nối phòng chat thành công!',
            'data'    => [
                'chat_info' => $chat,
                'messages'  => $messages
            ]
        ], 200);
    }

    /**
     * Gửi tin nhắn (Dùng chung cho cả Khách hàng và Nhân viên CSKH)
     */
    public function sendMessage(Request $request, $chatId)
    {
        $validator = Validator::make($request->all(), [
            'message_text' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 400);
        }

        // Kiểm tra phòng chat có tồn tại và đang mở không
        $chat = Chat::find($chatId);
        if (!$chat || $chat->status === 'Closed') {
            return response()->json(['success' => false, 'message' => 'Phòng chat không tồn tại hoặc đã đóng.'], 404);
        }

        // Tạo tin nhắn mới lưu vào database
        $message = ChatMessage::create([
            'chat_id'      => $chatId,
            'sender_id'    => auth()->id(),
            'message_text' => $request->message_text,
            'is_read'      => false,
            'sent_at'      => now()
        ]);

        // Phát sóng sự kiện Realtime bằng Laravel Event
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Gửi tin nhắn thành công!',
            'data'    => $message->load('sender:user_id,fullname,avatar_url')
        ], 201);
    }

    /**
     * [Dành cho STAFF] Lấy danh sách tất cả các phòng chat đang chờ xử lý hoặc đang hoạt động
     */
    public function getActiveChats()
    {
        $chats = Chat::where('status', 'Active')
            ->with(['customer:user_id,fullname,avatar_url', 'staff:user_id,fullname'])
            ->orderBy('conversation_start', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $chats], 200);
    }

    /**
     * [Dành cho STAFF] Tiếp nhận phòng chat của khách hàng
     */
    public function acceptChat($chatId)
    {
        $chat = Chat::find($chatId);
        if (!$chat || $chat->status === 'Closed') {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phòng chat hoặc phòng đã đóng.'], 404);
        }

        // Cập nhật staff_id là ID của nhân viên đang nhấn nhận chat
        $chat->update([
            'staff_id' => auth()->id()
        ]);

        return response()->json(['success' => true, 'message' => 'Bạn đã tiếp nhận phòng chat này thành công!', 'data' => $chat], 200);
    }

    /**
     * [Dành cho STAFF/ADMIN] Đóng phòng chat khi kết thúc tư vấn
     */
    public function closeChat($chatId)
    {
        $chat = Chat::find($chatId);
        if (!$chat) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy phòng chat.'], 404);
        }

        $chat->update([
            'status' => 'Closed'
        ]);

        return response()->json(['success' => true, 'message' => 'Đã đóng phòng chat thành công!'], 200);
    }
}