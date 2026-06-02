<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlowerMeaningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Kiểm tra trùng tên hoa trỏ chính xác về bảng flower_meaning số ít
            'flower_name'           => 'required|string|max:100|unique:flower_meaning,flower_name',
            'meaning'               => 'required|string',
            'symbolism'             => 'nullable|string',
            // Đồng bộ bộ 7 Enum thực tế trong DB của nhóm (Bao gồm cả Admiration)
            'emotion'               => 'required|in:Love,Joy,Sympathy,Gratitude,Congratulations,Apology,Admiration',
            'color_associations'    => 'nullable|array',
            'cultural_significance' => 'nullable|string',
            'care_instructions'     => 'nullable|string',
        ];
    }
}
