<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class DeliveryController extends Controller
{
    /**
     * 1. Shipper xem danh sách các đơn hàng đang chờ giao (Trạng thái Pending và chưa có ai nhận)
     */
    public function getAvailableDeliveries(): JsonResponse
    {
        $deliveries = DB::table('delivery')
            ->join('order', 'delivery.order_id', '=', 'order.order_id')
            ->where('delivery.status', 'Pending')
            ->whereNull('delivery.staff_id')
            ->select(
                'delivery.delivery_id',
                'delivery.order_id',
                'delivery.delivery_address',
                'delivery.estimated_date',
                'order.total_price',
                'order.shipping_fee',
                'order.final_price',
                'order.payment_method'
            )
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Tải danh sách đơn hàng chờ giao thành công.',
            'data'    => $deliveries
        ], Response::HTTP_OK);
    }

    /**
     * 2. Shipper chấp nhận nhận phụ trách giao một đơn hàng công khai
     */
    public function acceptDelivery(Request $request, $id): JsonResponse
    {
        // Kiểm tra xem đơn hàng vận chuyển có tồn tại không
        $delivery = DB::table('delivery')->where('order_id', $id)->first();

        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy thông tin vận chuyển của đơn hàng này.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Kiểm tra xem đã có shipper nào khác nhanh tay nhận trước chưa
        if ($delivery->staff_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này đã có nhân viên khác nhận phụ trách.'
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            $staffId = $request->user()->user_id; // Lấy ID của nhân viên từ Token Sanctuam

            // Cập nhật trạng thái trong bảng vận chuyển (delivery) sang 'In Transit'
            DB::table('delivery')
                ->where('order_id', $id)
                ->update([
                    'staff_id'   => $staffId,
                    'status'     => 'In Transit',
                    'updated_at' => now()
                ]);

            // Đồng bộ trạng thái đơn hàng (order) sang 'Shipped' (Đang đi giao)
            DB::table('order')
                ->where('order_id', $id)
                ->update([
                    'status'     => 'Shipped',
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bạn đã nhận đơn hàng số ' . $id . ' thành công. Hãy lên đường giao hoa an toàn!'
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Xảy ra lỗi khi tiếp nhận đơn hàng.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * 3. Shipper cập nhật trạng thái tiến độ giao (Thành công / Thất bại)
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:In Transit,Delivered,Failed',
            'notes'  => 'nullable|string'
        ]);

        $newStatus = $request->input('status');
        $notes = $request->input('notes');
        $staffId = $request->user()->user_id;

        // Kiểm tra tính hợp lệ của đơn hàng và quyền phụ trách
        $delivery = DB::table('delivery')->where('order_id', $id)->first();

        if (!$delivery) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn vận chuyển không tồn tại.'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($delivery->staff_id !== $staffId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền cập nhật trạng thái cho đơn hàng do người khác phụ trách.'
            ], Response::HTTP_FORBIDDEN);
        }

        DB::beginTransaction();
        try {
            // Chuẩn bị mảng update bảng delivery
            $deliveryUpdate = [
                'status'     => $newStatus,
                'notes'      => $notes,
                'updated_at' => now()
            ];

            // Nếu giao thành công, điền thêm ngày hoàn thành thực tế (actual_date)
            if ($newStatus === 'Delivered') {
                $deliveryUpdate['actual_date'] = now();
            }

            DB::table('delivery')->where('order_id', $id)->update($deliveryUpdate);

            // Đồng bộ trạng thái cuối cùng tương ứng sang bảng order
            // Nếu 'Delivered' -> bảng Order chuyển thành 'Delivered' (Trực tiếp kích hoạt Trigger cộng điểm tích lũy của nhóm)
            // Nếu 'Failed' -> bảng Order giữ hoặc chuyển sang 'Processing' tùy cấu trúc xử lý lại đơn
            $orderStatus = ($newStatus === 'Delivered') ? 'Delivered' : 'Processing';

            DB::table('order')
                ->where('order_id', $id)
                ->update([
                    'status'     => $orderStatus,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật tiến độ giao hàng thành công!',
                'current_status' => $newStatus
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống khi cập nhật trạng thái giao hàng.',
                'error'   => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
