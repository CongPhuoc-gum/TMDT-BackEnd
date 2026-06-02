<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Bật lên true để cho phép người dùng thực hiện
    }

    public function rules(): array
    {
        return [
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'is_default' => 'nullable|boolean',
        ];
    }
}