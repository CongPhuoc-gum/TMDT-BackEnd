<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:product,product_id',
            'items.*.quantity'   => 'required|integer|min:1',
            'shipping_fee'       => 'required|numeric|min:0',
            'coupon_code'        => 'nullable|string|exists:coupon,coupon_code',
            
            // Thông tin giao hàng của khách
            'customer_name'      => 'required|string|max:255',
            'customer_phone'     => 'required|string|max:20',
            'shipping_address'   => 'required|string|max:500',
            'payment_method'     => 'required|string|in:COD,VNPAY,MOMO', 
            'notes'              => 'nullable|string|max:500'
        ];
    }
}