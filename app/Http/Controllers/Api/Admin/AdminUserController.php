<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Staff;
use App\Http\Requests\Admin\StoreStaffRequest; // Hãy chắc chắn bạn đã tạo file này để validate Staff

class AdminUserController extends Controller
{
    /**
     * API Lấy danh sách người dùng hệ thống
     */
    public function index(): JsonResponse
    {
        $users = User::with('staff')->get();
        return response()->json(['success' => true, 'data' => $users]);
    }

    /**
     * API Admin tạo tài khoản nhân viên mới (Gom tất cả chức năng linh hoạt về một mối Staff)
     */
    public function storeStaff(StoreStaffRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            // 1. Tạo tài khoản đăng nhập ở bảng gốc User
            $user = User::create([
                'fullname'  => $request->input('full_name'),
                'email'     => $request->input('email'),
                'password'  => Hash::make($request->input('password')),
                'phone'     => $request->input('phone'),
                'role'      => 'Staff', // Sử dụng Role chung để phân quyền bằng Middleware staff
                'is_active' => true,
            ]);

            // 2. Lưu thông tin hồ sơ nhân viên thực tế sang bảng Staff, gán staff_id bằng user_id mới sinh
            Staff::create([
                'staff_id'        => $user->user_id,
                'position'        => $request->input('position'), // Designer, Florist, Customer_Support, Delivery_Staff
                'department'      => $request->input('department', 'Bộ Phận Vận Hành'),
                'salary'          => $request->input('salary', 0),
                'hire_date'       => now()->toDateString(),
                'specializations' => $request->input('specializations'), // Nhận dạng mảng JSON
                'availability'    => 1, // Mặc định sẵn sàng làm việc
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo tài khoản nhân viên hệ thống thành công.',
                'data'    => $user->load('staff')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi tạo tài khoản nhân viên.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bật/Tắt trạng thái hoạt động tài khoản
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người dùng.'], 404);
        }
        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái thành công.', 'data' => $user]);
    }

    /**
     * Xóa tài khoản người dùng
     */
    public function destroy(int $id): JsonResponse
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy người dùng.'], 404);
        }
        $user->delete();
        return response()->json(['success' => true, 'message' => 'Xóa người dùng thành công.']);
    }
}
