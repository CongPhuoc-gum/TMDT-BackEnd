<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreAddressRequest;
use App\Http\Requests\Customer\UpdateProfileRequest;
use App\Models\ShippingAddress; 
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerProfileController extends Controller
{
    // 1. Lấy danh sách sổ địa chỉ của khách hàng đang đăng nhập
    public function getAddresses(Request $request): JsonResponse
    {
        // Lấy ID khách hàng từ Token Sanctum bóc ra
        $userId = $request->user()->user_id; 

        $addresses = ShippingAddress::where('customer_id', $userId)->latest()->get();
        
        return response()->json([
            'success' => true,
            'data' => $addresses,
            'message' => 'Lấy danh sách sổ địa chỉ thành công.'
        ]);
    }

    // 2. Thêm địa chỉ mới
    public function storeAddress(StoreAddressRequest $request): JsonResponse
    {
        $userId = $request->user()->user_id;

        // Logic Senior: Nếu địa chỉ này được chọn làm mặc định, tự động bỏ mặc định các địa chỉ cũ
        if ($request->input('is_default', false) == true) {
            ShippingAddress::where('customer_id', $userId)->update(['is_default' => false]);
        }

        $address = ShippingAddress::create([
            'customer_id' => $userId,
            'receiver_name' => $request->input('receiver_name'),
            'receiver_phone' => $request->input('receiver_phone'),
            'address' => $request->input('address'),
            'city' => $request->input('city'),
            'is_default' => $request->input('is_default', false),
        ]);

        return response()->json([
            'success' => true,
            'data' => $address,
            'message' => 'Thêm địa chỉ giao hàng thành công.'
        ], 201);
    }
}