<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\WishlistRequest;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index(): JsonResponse
    {
        $wishlist = Wishlist::where('customer_id', Auth::id())
            ->with('product.images')
            ->get();
        return response()->json(['success' => true, 'data' => $wishlist]);
    }

    public function store(WishlistRequest $request): JsonResponse
    {
        // Chặn trùng lặp nhờ ràng buộc unique_customer_wishlist
        $wishlist = Wishlist::firstOrCreate([
            'customer_id' => Auth::id(),
            'product_id'  => $request->input('product_id')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào danh sách yêu thích.',
            'data' => $wishlist
        ], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        $wishlist = Wishlist::where('customer_id', Auth::id())
            ->where('wishlist_id', $id)
            ->firstOrFail();
        $wishlist->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa khỏi danh sách yêu thích.']);
    }
}
