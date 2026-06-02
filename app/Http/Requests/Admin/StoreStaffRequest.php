<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    public function authorize(): bool { return true; }

   public function rules(): array
{
    return [
        'email'     => 'required|email|unique:user,email|max:100',
        'password'  => 'required|string|min:6',
        'phone'     => 'nullable|string|max:20',
        'full_name' => 'required|string|max:100',
        'position'  => 'required|string', // Để chuỗi tự do, Admin truyền gì lên cũng được
        'salary'    => 'nullable|numeric|min:0',
    ];
}
}