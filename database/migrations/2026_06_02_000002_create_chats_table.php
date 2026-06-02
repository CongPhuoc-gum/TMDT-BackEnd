<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Kiểm tra và tạo bảng Chat nếu chưa có
        if (!Schema::hasTable('chat')) {
            Schema::create('chat', function (Blueprint $table) {
                $table->id('chat_id');
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('staff_id')->nullable();
                $table->enum('status', ['Active', 'Closed'])->default('Active');
                
                // Giữ lại cấu trúc timestamps phòng trường hợp bạn cần dùng trong tương lai
                $table->timestamps();

                $table->foreign('customer_id')->references('user_id')->on('user')->onDelete('cascade');
                $table->foreign('staff_id')->references('user_id')->on('user')->onDelete('set null');
            });
        }

        // 2. Kiểm tra và tạo bảng Chat Message nếu chưa có
        if (!Schema::hasTable('chat_message')) {
            Schema::create('chat_message', function (Blueprint $table) {
                $table->id('message_id');
                $table->unsignedBigInteger('chat_id');
                $table->unsignedBigInteger('sender_id');
                $table->text('message');
                $table->timestamps();

                $table->index('chat_id');

                $table->foreign('chat_id')->references('chat_id')->on('chat')->onDelete('cascade');
                $table->foreign('sender_id')->references('user_id')->on('user')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message');
        Schema::dropIfExists('chat');
    }
};