<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cho phép thực thi nếu đã vượt qua Middleware Admin
    }

public function rules(): array
    {
        return [
            'product_name'   => 'required|string|max:150',
            'description'    => 'nullable|string',
            'price'          => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id'    => 'required|integer',
            'flower_meaning_id' => 'nullable|integer',
            'images'         => 'required|array|min:1',
            'images.*'       => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}