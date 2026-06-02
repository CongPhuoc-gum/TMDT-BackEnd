<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:product,product_id', // Đã map chuẩn theo bảng product của bạn
            'items.*.quantity'   => 'required|integer|min:1',
            'coupon_code'        => 'nullable|string|exists:coupon,coupon_code',
            'shipping_fee'       => 'required|numeric|min:0',
        ];
    }
}