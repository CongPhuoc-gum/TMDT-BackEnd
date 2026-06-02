<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\OrderAnalytics;
use Carbon\Carbon;

class AggregateDailyAnalytics extends Command
{
    protected $signature = 'analytics:aggregate {date?}';
    protected $description = 'Tổng hợp doanh thu và sản phẩm bán chạy nhất cuối ngày';

    public function handle()
    {
        // Nếu không truyền ngày, mặc định lấy ngày hôm nay
        $targetDate = $this->argument('date')
            ? Carbon::parse($this->argument('date'))->toDateString()
            : Carbon::today()->toDateString();

        $this->info("Bắt đầu tổng hợp dữ liệu cho ngày: {$targetDate}");

        // 1. Tính tổng doanh thu và tổng số đơn hàng đã hoàn thành (Delivered)
        $orderMetrics = DB::table('order')
            ->whereDate('created_at', $targetDate)
            ->where('status', 'Delivered')
            ->select(
                DB::raw('SUM(total_price) as total_revenue'),
                DB::raw('COUNT(order_id) as total_orders')
            )->first();

        $totalRevenue = $orderMetrics->total_revenue ?? 0.00;
        $totalOrders = $orderMetrics->total_orders ?? 0;

        // 2. Tìm danh sách sản phẩm bán chạy nhất trong ngày (Top 5 sản phẩm)
        $bestselling = DB::table('order_item')
            ->join('order', 'order_item.order_id', '=', 'order.order_id')
            ->join('product', 'order_item.product_id', '=', 'product.product_id')
            ->whereDate('order.created_at', $targetDate)
            ->where('order.status', 'Delivered')
            ->select(
                'order_item.product_id',
                'product.name',
                DB::raw('SUM(order_item.quantity) as total_quantity_sold'),
                DB::raw('SUM(order_item.price * order_item.quantity) as total_product_revenue')
            )
            ->groupBy('order_item.product_id', 'product.name')
            ->orderByDesc('total_quantity_sold')
            ->limit(5)
            ->get();

        // 3. Cập nhật hoặc Tạo mới bản ghi Analytics
        OrderAnalytics::updateOrCreate(
            ['date' => $targetDate],
            [
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'bestselling_products' => $bestselling->toArray() // Tự động hóa thành chuỗi JSON nhờ $casts của Model
            ]
        );

        $this->info("Tổng hợp dữ liệu thành công! Doanh thu: " . number_format($totalRevenue) . " VNĐ");
    }
}
