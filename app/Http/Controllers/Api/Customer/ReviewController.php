<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreReviewRequest;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request): JsonResponse
    {
        // Hệ thống đã cài Trigger tự động tính toán rating trung bình, 
        // ở tầng code ta chỉ cần thực hiện ghi nhận INSERT sạch sẽ vào bảng review.
        $review = Review::create([
            'order_id'    => $request->order_id,
            'product_id'  => $request->product_id,
            'customer_id' => Auth::id(),
            'rating'      => $request->rating,
            'comment'     => $request->comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã đánh giá sản phẩm!',
            'data' => $review
        ], 201);
    }
}