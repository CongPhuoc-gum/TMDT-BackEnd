<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Hệ Thống Cyberbloom
|--------------------------------------------------------------------------
*/

/* --- Các Route Công Khai (Không cần Token) --- */
/* --- Các Route Công Khai (Không cần Token) --- */
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);

    // Luồng Đăng nhập bằng Google OAuth2
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

    // 🌸 ĐÃ SỬA: Xóa chữ /auth bị trùng để Postman nhận đúng đường dẫn chuẩn
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-password-otp', [AuthController::class, 'verifyPasswordOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

/* --- Các Route Bảo Mật (Yêu cầu phải đăng nhập qua Sanctum Token) --- */
Route::middleware('auth:sanctum')->group(function () {
    
    // Phân quyền độc quyền cho Quản trị viên (Admin)
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Chỉ tài khoản có Role là Admin mới có quyền gọi API tạo tài khoản cho Staff
        Route::post('/staff/create', [AuthController::class, 'createStaffAccount']);
    });

    // Phân quyền cho Nhân viên (Staff)
    Route::middleware('staff')->prefix('staff')->group(function () {
        // Các API xử lý đơn hàng, cắm hoa, chat... sẽ viết ở đây
    });

    // Phân quyền cho Khách hàng (Customer)
    Route::middleware('customer')->prefix('customer')->group(function () {
        // Các API giỏ hàng, xem đơn hàng cá nhân... sẽ viết ở đây
    });
});