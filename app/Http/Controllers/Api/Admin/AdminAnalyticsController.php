<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\OrderAnalytics;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class AdminAnalyticsController extends Controller
{
    /**
     * Lấy báo cáo tổng quan doanh thu và danh sách bán chạy theo khoảng thời gian
     */
    public function getReport(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Test thành công: Bạn đang truy cập API Analytics với quyền Admin!',
            'mock_data' => [
                'total_revenue' => 45000000,
                'total_orders' => 120,
                'bestselling_products' => [
                    ['product_id' => 1, 'name' => 'Hoa Hồng Đỏ Độc Bản', 'quantity' => 45]
                ]
            ]
        ], 200);
    }
}
