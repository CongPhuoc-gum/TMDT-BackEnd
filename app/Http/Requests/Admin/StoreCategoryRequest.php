<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category_name' => 'required|string|unique:category,category_name|max:100',
            'description'   => 'nullable|string',
        ];
    }
}