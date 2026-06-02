<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'                => 'required|string|max:50|unique:coupon,coupon_code',
            'discount_type'       => 'required|in:Percentage,FixedAmount,Shipping', // Thêm Shipping ở đây nếu bạn muốn dùng tính năng giảm ship nhé
            'value'               => 'required|numeric|min:0',
            'min_purchase_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit'         => 'required|integer|min:1',
            'start_date'          => 'required|date',
            'end_date'            => 'required|date|after_or_equal:start_date',
        ];
    }
}