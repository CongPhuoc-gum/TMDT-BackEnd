<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AddCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Hàm phụ trợ lấy hoặc tự tạo Giỏ hàng độc bản cho Customer
    private function getOrCreateCart(int $customerId): Cart
    {
        return Cart::firstOrCreate(['customer_id' => $customerId]);
    }

    public function viewCart(): JsonResponse
    {
        $cart = $this->getOrCreateCart(Auth::id());
        $items = $cart->items()->with('product.images')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'cart_id' => $cart->cart_id,
                'items' => $items,
                'total_price' => $this->calculateTotal($cart)
            ]
        ]);
    }

    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $customerId = Auth::id();
        $cart = $this->getOrCreateCart($customerId);

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');

        // Kiểm tra tồn kho trước khi cho thêm vào giỏ
        $product = Product::findOrFail($productId);
        if ($product->stock_quantity < $quantity) {
            return response()->json(['success' => false, 'message' => 'Số lượng kho không đủ.'], 400);
        }

        // Nếu sản phẩm đã tồn tại trong giỏ thì tăng số lượng, ngược lại tạo mới
        $cartItem = CartItem::where('cart_id', $cart->cart_id)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            if ($product->stock_quantity < ($cartItem->quantity + $quantity)) {
                return response()->json(['success' => false, 'message' => 'Tổng số lượng vượt quá kho.'], 400);
            }
            $cartItem->quantity += $quantity;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->cart_id,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng.',
            'data' => $cartItem
        ], 201);
    }

    public function updateItem(Request $request, int $itemId): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $cartItem = CartItem::whereHas('cart', function ($query) {
            $query->where('customer_id', Auth::id());
        })->findOrFail($itemId);

        // Check tồn kho
        if ($cartItem->product->stock_quantity < $request->quantity) {
            return response()->json(['success' => false, 'message' => 'Số lượng kho không đủ.'], 400);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật số lượng thành công.',
            'data' => $cartItem
        ]);
    }

    public function removeItem(int $itemId): JsonResponse
    {
        $cartItem = CartItem::whereHas('cart', function ($query) {
            $query->where('customer_id', Auth::id());
        })->findOrFail($itemId);

        $cartItem->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa mặt hàng khỏi giỏ.']);
    }

    public function clearCart(): JsonResponse
    {
        $cart = Cart::where('customer_id', Auth::id())->first();
        if ($cart) {
            CartItem::where('cart_id', $cart->cart_id)->delete();
        }

        return response()->json(['success' => true, 'message' => 'Đã làm trống giỏ hàng.']);
    }

    private function calculateTotal(Cart $cart): float
    {
        return (float) $cart->items->sum(function ($item) {
            // Ưu tiên lấy giá ưu đãi discount_price nếu có
            $price = $item->product->discount_price ?? $item->product->price;
            return $price * $item->quantity;
        });
    }
}
