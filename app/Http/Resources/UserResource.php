<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Giữ nguyên logic xác định vai trò dựa trên sự tồn tại của quan hệ 1:1 của bạn
        $role = 'Customer';
        if ($this->relationLoaded('admin') && $this->admin !== null) {
            $role = 'Admin';
        } elseif ($this->relationLoaded('staff') && $this->staff !== null) {
            $role = 'Staff';
        }

        return [
            'user_id'      => $this->user_id,
            'fullname'     => $this->fullname,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'avatar_url'   => $this->avatar_url,
            'is_active'    => $this->is_active,
            'role'         => $role,
            'member_since' => $this->created_at?->toIso8601String(),

            // ============================================================
            // BỔ SUNG THÊM ĐOẠN NÀY: Trả thêm dữ liệu chi tiết nếu có load quan hệ
            // ============================================================
            'profile_details' => $this->whenLoaded('staff', function () {
                return [
                    'position'  => $this->staff->position,
                    'salary'    => $this->staff->salary,
                    'hire_date' => $this->staff->hire_date,
                ];
            }) ?? $this->whenLoaded('customer', function () {
                return [
                    // Điền các trường mở rộng của khách hàng nếu database của bạn có (ví dụ: rank, điểm tích lũy...)
                    'customer_id' => $this->customer->customer_id, 
                ];
            }),
        ];
    }
}