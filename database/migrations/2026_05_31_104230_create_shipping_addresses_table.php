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
        // Thêm dòng này: Nếu thấy bảng cũ nằm chướng ngại vật thì tự động xóa sạch đi trước
        Schema::dropIfExists('shipping_addresses');

        Schema::create('shipping_addresses', function (Blueprint $blueprint) {
            $blueprint->id('address_id');
            
            // Chỉ tạo cột số nguyên bình thường để liên kết ID, 
            // bỏ ràng buộc khóa ngoại cứng để không bị bắt bẻ việc lệch thứ tự file migration cũ/mới
            $blueprint->unsignedBigInteger('customer_id');

            $blueprint->string('receiver_name', 255);
            $blueprint->string('receiver_phone', 20);
            $blueprint->string('address', 500);
            $blueprint->string('city', 100);
            $blueprint->boolean('is_default')->default(false);
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};