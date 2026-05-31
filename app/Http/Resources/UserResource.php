<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Xác định vai trò dựa trên sự tồn tại của quan hệ 1:1
        $role = 'Customer';
        if ($this->relationLoaded('admin') && $this->admin !== null) {
            $role = 'Admin';
        } elseif ($this->relationLoaded('staff') && $this->staff !== null) {
            $role = 'Staff';
        }

        return [
            'user_id'    => $this->user_id,
            'fullname'   => $this->fullname,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'avatar_url' => $this->avatar_url,
            'is_active'  => $this->is_active,
            'role'       => $role,
            'member_since' => $this->created_at?->toIso8601String(),
        ];
    }
}