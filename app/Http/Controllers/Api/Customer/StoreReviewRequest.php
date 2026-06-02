<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Order;

class StoreReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ràng buộc bảo mật chuyên sâu: Đơn hàng phải thuộc về chính khách hàng đó và phải ở trạng thái 'Delivered'
        $order = Order::where('order_id', $this->order_id)
            ->where('customer_id', $this->user()->user_id)
            ->first();
        return $order && $order->status === 'Delivered';
    }

    public function rules(): array
    {
        return [
            'order_id'   => 'required|integer|exists:order,order_id',
            'product_id' => 'required|integer|exists:product,product_id',
            'rating'     => 'required|integer|between:1,5', // Ép từ 1 đến 5 sao chuẩn DB CHECK constraint
            'comment'    => 'nullable|string',
        ];
    }
}
