<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class StoreReviewRequest extends FormRequest
{
    /**
     * Xác thực quyền gửi đánh giá
     */
    public function authorize(): bool
    {
        // Kiểm tra bảo mật: Đơn hàng phải thuộc về chính khách hàng đang đăng nhập và đã giao thành công (Delivered)
        $customerId = $this->user()->user_id;
        $orderId = $this->input('order_id');

        $orderExists = DB::table('order')
            ->where('order_id', $orderId)
            ->where('customer_id', $customerId)
            ->where('status', 'Delivered')
            ->exists();

        return $orderExists;
    }

    /**
     * Các quy định kiểm tra dữ liệu đầu vào (Validation Rules)
     */
    public function rules(): array
    {
        return [
            'order_id'   => 'required|integer|exists:order,order_id',
            'product_id' => 'required|integer|exists:product,product_id',
            'rating'     => 'required|integer|between:1,5', // Giới hạn từ 1 đến 5 sao theo thiết kế hệ thống
            'comment'    => 'nullable|string',
        ];
    }

    /**
     * Tùy biến thông báo lỗi trả về dạng tiếng Việt trực quan
     */
    public function messages(): array
    {
        return [
            'order_id.required'   => 'Mã đơn hàng không được để trống.',
            'order_id.exists'     => 'Đơn hàng không tồn tại trong hệ thống.',
            'product_id.required' => 'Mã sản phẩm không được để trống.',
            'product_id.exists'   => 'Sản phẩm không tồn tại.',
            'rating.required'     => 'Vui lòng chọn số sao đánh giá.',
            'rating.between'      => 'Điểm đánh giá phải nằm trong khoảng từ 1 đến 5 sao.',
        ];
    }
}
