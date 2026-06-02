<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\OccasionReminder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OccasionReminderController extends Controller
{
    public function index(): JsonResponse
    {
        $reminders = OccasionReminder::where('customer_id', Auth::id())->get();
        return response()->json(['success' => true, 'data' => $reminders]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'occasion_name'        => 'required|string|max:100',
            'occurrence_date'      => 'required|date',
            'reminder_days_before' => 'nullable|integer|min:1|max:30',
            'is_recurring'         => 'nullable|boolean',
            'message'              => 'nullable|string'
        ]);

        $reminder = OccasionReminder::create([
            'customer_id'          => Auth::id(),
            'occasion_name'        => $request->occasion_name,
            'occurrence_date'      => $request->occurrence_date,
            'reminder_days_before' => $request->input('reminder_days_before', 7),
            'is_recurring'         => $request->input('is_recurring', true),
            'message'              => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu ngày kỷ niệm. Hệ thống sẽ thông báo trước cho bạn!',
            'data' => $reminder
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $reminder = OccasionReminder::where('customer_id', Auth::id())->findOrFail($id);
        $reminder->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa lịch nhắc nhở kỷ niệm này.']);
    }
}
