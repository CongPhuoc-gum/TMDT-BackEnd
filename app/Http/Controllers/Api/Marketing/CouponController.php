<?php

namespace App\Http\Controllers\Api\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCouponRequest;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    public function index(): JsonResponse
    {
        $coupons = Coupon::latest()->get();
        return response()->json([
            'success' => true,
            'data' => $coupons,
            'message' => 'Lấy danh sách mã giảm giá thành công.'
        ], 200);
    }

    public function store(StoreCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::create([
            'coupon_code'           => strtoupper($request->input('code')),
            'admin_id'              => Auth::id() ?? 1, // Lấy ID của Admin đang login, nếu chưa có token thì mặc định là 1
            'discount_type'         => $request->input('discount_type'),
            'discount_value'        => $request->input('value'),
            'min_purchase_amount'   => $request->input('min_purchase_amount', 0),
            'max_discount_amount'   => $request->input('max_discount_amount'),
            'usage_limit'           => $request->input('usage_limit'),
            'current_usage'         => 0,
            'applicable_categories' => json_encode($request->input('applicable_categories', [])), // Lưu mảng JSON danh mục áp dụng
            'start_date'            => $request->input('start_date'),
            'end_date'              => $request->input('end_date'),
            'is_active'             => 1,
        ]);

        return response()->json([
            'success' => true,
            'data' => $coupon,
            'message' => 'Phát hành mã giảm giá mới thành công.'
        ], 201);
    }

    public function destroy($id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa bỏ mã giảm giá thành công.'
        ], 200);
    }
}