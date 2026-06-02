<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Models\CustomRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DesignerController extends Controller
{
    // Lấy toàn bộ danh sách yêu cầu thiết kế chưa được xử lý hoặc do chính mình phụ trách
    public function getPendingRequests(): JsonResponse
    {
        $requests = CustomRequest::where('status', 'Pending')
            ->orWhere('staff_id', Auth::id())
            ->get();
        return response()->json(['success' => true, 'data' => $requests]);
    }

    // Designer cập nhật ghi chú thiết kế và báo giá cuối cùng gửi lại cho khách duyệt
    public function updateQuotation(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'design_notes' => 'required|string',
            'quotation'    => 'required|numeric|min:0',
            'status'       => 'required|in:In Progress,Completed,Rejected'
        ]);

        $customRequest = CustomRequest::findOrFail($id);

        $customRequest->update([
            'staff_id'     => Auth::id(), // Ghi nhận designer chịu trách nhiệm
            'design_notes' => $request->design_notes,
            'quotation'    => $request->quotation,
            'status'       => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật báo giá và phương án thiết kế thành công.',
            'data' => $customRequest
        ]);
    }
}
