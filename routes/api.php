<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Catalog\CatalogController;
use App\Http\Controllers\Api\Admin\FlowerMeaningController;
use App\Http\Controllers\Api\Marketing\CouponController;
use App\Http\Controllers\Api\Customer\CustomerProfileController;
use App\Http\Controllers\Api\Order\CheckoutController;

// Import đầy đủ các phân hệ xử lý nghiệp vụ nước rút
use App\Http\Controllers\Api\Customer\CartController;
use App\Http\Controllers\Api\Customer\WishlistController;
use App\Http\Controllers\Api\Customer\ReviewController;
use App\Http\Controllers\Api\Customer\CustomRequestController;
use App\Http\Controllers\Api\Customer\OccasionReminderController;
use App\Http\Controllers\Api\Staff\DeliveryController;
use App\Http\Controllers\Api\Staff\DesignerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Hệ Thống Cyberbloom (Laravel 12.x)
| API Routes - Hệ Thống Cyberbloom (Laravel 12.x)
|--------------------------------------------------------------------------
*/

/* --- 1. Các Route Công Khai Hoàn Toàn (Không cần Token) --- */

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);

    // Luồng Đăng nhập bằng Google OAuth2
    Route::get('/google/redirect', [GoogleAuthController::class, 'redirectToGoogle']);
    Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

    // Khôi phục mật khẩu công khai
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-password-otp', [AuthController::class, 'verifyPasswordOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// API công khai cho khách xem và lọc danh sách sản phẩm hoa tươi ngoài trang chủ
Route::get('/products', [CatalogController::class, 'getProductsForCustomer']);

// API công khai để lấy danh sách ý nghĩa hoa/cảm xúc hiển thị ngoài bộ lọc Trang chủ hoặc trang Tự thiết kế hoa
Route::get('/flower-meanings', [FlowerMeaningController::class, 'index']);

// API công khai để lấy danh sách ý nghĩa hoa/cảm xúc hiển thị ngoài bộ lọc Trang chủ hoặc trang Tự thiết kế hoa
Route::get('/flower-meanings', [FlowerMeaningController::class, 'index']);


/* =========================================================================
 * 🛒 CỤM ROUTE CHECKOUT & THANH TOÁN (Công khai hoàn toàn, xử lý Token động trong Controller)
 * ========================================================================= */
Route::prefix('checkout')->group(function () {
    Route::post('/validate', [CheckoutController::class, 'calculateOrder']); // Xem trước hóa đơn, tính giảm giá
    Route::post('/place-order', [CheckoutController::class, 'placeOrder']);   // Đặt hàng chính thức để lấy link VNPAY/Stripe

    // Các Route Webhook/Callback nhận tín hiệu từ cổng thanh toán ngân hàng
    Route::get('/vnpay-callback', [CheckoutController::class, 'vnpayCallback']);
    Route::get('/stripe-callback', [CheckoutController::class, 'stripeCallback']);
});


/* --- 2. Các Route Bảo Mật Nghiêm Ngặt (Bắt buộc phải đăng nhập và phân đúng quyền) --- */
Route::middleware('auth:sanctum')->group(function () {

    /* =========================================================================
     * [ADMIN] Phân quyền độc quyền cho Quản trị viên
     * ========================================================================= */
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Luồng quản lý nhân sự cũ
        Route::post('/staff/create', [AuthController::class, 'createStaffAccount']);

        // Quản lý danh sách Người dùng & Nhân viên cấp cao (AdminUserController)
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/staff/store', [AdminUserController::class, 'storeStaff']);
        Route::patch('/users/{id}/toggle-status', [AdminUserController::class, 'toggleStatus']);
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);

        // CRUD Danh mục (Category) - Tận dụng file CatalogController của bạn
        Route::post('/categories', [CatalogController::class, 'storeCategory']);
        Route::put('/categories/{id}', [CatalogController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [CatalogController::class, 'destroyCategory']);

        // CRUD Sản phẩm hoa tươi đa phương tiện (CatalogController)
        Route::post('/products', [CatalogController::class, 'storeProduct']);
        Route::post('/products/{id}', [CatalogController::class, 'updateProduct']); // Dùng POST nhận Multipart-Data tốt hơn
        Route::delete('/products/{id}', [CatalogController::class, 'destroyProduct']);

        // CRUD Quản lý Ý nghĩa hoa chuyên sâu (Phục vụ đặt hoa theo yêu cầu / Gợi ý thông điệp cảm xúc)
        Route::apiResource('/flower-meanings', FlowerMeaningController::class)->except(['index']);

        // Phát hành mã giảm giá Marketing
        Route::post('/coupons', [CouponController::class, 'store']);
        Route::delete('/coupons/{id}', [CouponController::class, 'destroy']);
    });

    /* =========================================================================
     * [STAFF] Phân quyền cho Nhân viên (Designer, Florist, CS, Delivery)
     * ========================================================================= */
    Route::middleware('staff')->prefix('staff')->group(function () {
        // Cho phép nhân viên xem danh sách mã giảm giá để tư vấn cho khách
        Route::get('/coupons', [CouponController::class, 'index']);

        // --- NHÓM 2: VẬN CHUYỂN (Đồng bộ tham số {orderId} trùng với Database và Controller) ---
        Route::prefix('delivery')->group(function () {
            Route::get('/available', [DeliveryController::class, 'getAvailableDeliveries']);
            Route::post('/{orderId}/accept', [DeliveryController::class, 'acceptDelivery']);
            Route::patch('/{orderId}/status', [DeliveryController::class, 'updateStatus']);
        });

        // --- NHÓM 3: DỊCH VỤ THIẾT KẾ (Đồng bộ tham số {customRequestId} trùng với Controller) ---
        Route::prefix('designer')->group(function () {
            Route::get('/requests/pending', [DesignerController::class, 'getPendingRequests']);
            Route::patch('/requests/{customRequestId}/quote', [DesignerController::class, 'updateQuotation']);
        });
    });

    /* =========================================================================
     * [CUSTOMER] Phân quyền cho Khách hàng đã đăng nhập tài khoản
     * ========================================================================= */
    Route::middleware('customer')->prefix('customer')->group(function () {
        // Cập nhật thông tin cá nhân, đổi mật khẩu & Sổ địa chỉ giao hàng
        Route::post('/profile/update', [CustomerProfileController::class, 'updateProfile']);
        Route::get('/addresses', [CustomerProfileController::class, 'getAddresses']);
        Route::post('/addresses', [CustomerProfileController::class, 'storeAddress']);
        Route::delete('/addresses/{id}', [CustomerProfileController::class, 'destroyAddress']);

        // --- NHÓM 1: GIỎ HÀNG (Cart) & WISHLIST RESTful API ---
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'viewCart']);
            Route::post('/add', [CartController::class, 'addItem']);
            Route::put('/items/{itemId}', [CartController::class, 'updateItem']);
            Route::delete('/items/{itemId}', [CartController::class, 'removeItem']);
            Route::delete('/clear', [CartController::class, 'clearCart']);
        });

        // CRUD Danh sách yêu thích độc bản 
        Route::apiResource('wishlist', WishlistController::class)->only(['index', 'store', 'destroy']);

        // --- NHÓM 2: ĐÁNH GIÁ (Review) ---
        Route::post('reviews', [ReviewController::class, 'store']);

        // --- NHÓM 3: DỊCH VỤ ĐẶC THÙ (Custom Request & Occasion Reminder) ---
        Route::get('custom-requests', [CustomRequestController::class, 'indexForCustomer']);
        Route::post('custom-requests', [CustomRequestController::class, 'store']);

        // Lưu trữ ngày kỷ niệm và cài đặt ngày nhận thông báo nhắc nhở tự động
        Route::apiResource('occasion-reminders', OccasionReminderController::class)->only(['index', 'store', 'destroy']);
    });
});
