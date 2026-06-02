<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Kiểm tra nếu bảng chưa tồn tại trong Database thì mới tiến hành tạo
        if (!Schema::hasTable('order_analytics')) {
            Schema::create('order_analytics', function (Blueprint $table) {
                $table->id('analytics_id');
                $table->date('date')->unique(); // Mỗi ngày chỉ có 1 dòng tổng hợp duy nhất
                $table->decimal('total_revenue', 15, 2)->default(0.00);
                $table->integer('total_orders')->default(0);
                $table->json('bestselling_products')->nullable(); // Lưu trữ dạng JSON: [{"product_id": 1, "name": "Hoa Hồng", "qty": 15}]
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_analytics');
    }
};