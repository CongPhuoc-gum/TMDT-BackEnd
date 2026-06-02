<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Chạy migration để thêm cột.
     */
    public function up(): void
    {
        Schema::table('user', function (Blueprint $blueprint) {
            $blueprint->softDeletes(); // Tự động thêm cột 'deleted_at' vào bảng user
        });
    }

    /**
     * Đảo ngược migration (Xóa cột).
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $blueprint) {
            $blueprint->dropSoftDeletes(); // Xóa cột 'deleted_at' nếu rollback
        });
    }
};