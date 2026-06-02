<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'               => 'required|string|max:150',
            'description'         => 'nullable|string',
            'palette_id'          => 'nullable|integer|exists:color_palette,palette_id',
            'budget'              => 'nullable|numeric|min:0',
            'occasion'            => 'nullable|string|max:100',
            'delivery_date'       => 'nullable|date|after:today',
            'reference_image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096' // Xử lý upload ảnh mẫu thật
        ];
    }
}
