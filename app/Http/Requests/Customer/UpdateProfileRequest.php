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
    /**
     * 1. API cập nhật thông tin cá nhân & Đổi mật khẩu bảo mật
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user(); // Lấy thông tin User đang đăng nhập từ Token

        // Cập nhật các trường cơ bản nếu có truyền lên
        if ($request->has('fullname')) {
            $user->fullname = $request->input('fullname');
        }
        if ($request->has('phone')) {
            $user->phone = $request->input('phone');
        }

        // Xử lý upload ảnh đại diện vào thư mục Storage công khai
        if ($request->hasFile('avatar')) {
            // Nếu tài khoản đã có avatar cũ, tiến hành xóa file vật lý cũ đi để tránh rác server
            if ($user->avatar_url) {
                $oldPath = str_replace('/storage/', '', parse_url($user->avatar_url, PHP_URL_PATH));
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            // Lưu file mới vào thư mục 'public/avatars'
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_url = Storage::disk('public')->url($path);
        }

        // Xử lý logic đổi mật khẩu: Xác thực mật khẩu cũ trước khi ghi đè mật khẩu mới
        if ($request->has('new_password')) {
            if (!Hash::check($request->input('current_password'), $user->password_hash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu hiện tại không chính xác.'
                ], 422);
            }
            // Mã hóa mật khẩu mới và ghi đè vào cột password_hash
            $user->password_hash = Hash::make($request->input('new_password'));
        }

        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->user_id,
                'fullname' => $user->fullname,
                'email' => $user->email,
                'phone' => $user->phone,
                'avatar_url' => $user->avatar_url
            ],
            'message' => 'Cập nhật hồ sơ tài khoản thành công.'
        ]);
    }

    /**
     * 2. API lấy danh sách sổ địa chỉ nhận hoa
     */
    public function getAddresses(Request $request): JsonResponse
    {
        $userId = $request->user()->user_id; 

        $addresses = ShippingAddress::where('customer_id', $userId)->latest()->get();
        
        return response()->json([
            'success' => true,
            'data' => $addresses,
            'message' => 'Lấy danh sách sổ địa chỉ thành công.'
        ]);
    }

    /**
     * 3. API thêm địa chỉ nhận hoa mới
     */
    public function storeAddress(StoreAddressRequest $request): JsonResponse
    {
        $userId = $request->user()->user_id;

        // Nếu khách hàng chọn địa chỉ này làm mặc định (is_default = true), 
        // tự động đặt tất cả các địa chỉ cũ của khách hàng này về false trước.
        if ($request->input('is_default', false) == true) {
            ShippingAddress::where('customer_id', $userId)->update(['is_default' => false]);
        }

        $address = ShippingAddress::create([
            'customer_id'    => $userId,
            'receiver_name'  => $request->input('receiver_name'),
            'receiver_phone' => $request->input('receiver_phone'),
            'address'        => $request->input('address'),
            'city'           => $request->input('city'),
            'is_default'     => $request->input('is_default', false),
        ]);

        return response()->json([
            'success' => true,
            'data' => $address,
            'message' => 'Thêm địa chỉ giao hàng thành công.'
        ], 201);
    }

    /**
     * 4. API xóa địa chỉ khỏi sổ địa chỉ
     */
    public function destroyAddress(Request $request, $id): JsonResponse
    {
        $userId = $request->user()->user_id;

        // Chỉ cho phép xóa đúng địa chỉ thuộc sở hữu của mình để tránh hack chéo ID
        $address = ShippingAddress::where('customer_id', $userId)->where('address_id', $id)->firstOrFail();
        $address->delete(); // Chạy Soft Delete bảo toàn lịch sử

        return response()->json([
            'success' => true,
            'message' => 'Xóa địa chỉ thành công khỏi sổ địa chỉ.'
        ]);
    }
}