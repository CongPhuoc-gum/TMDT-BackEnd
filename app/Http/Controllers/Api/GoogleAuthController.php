<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class GoogleAuthController extends BaseController
{
    /**
     * Điều hướng người dùng sang trang đăng nhập của Google
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Tiếp nhận gói dữ liệu phản hồi từ Google gửi về
     */
    public function handleGoogleCallback(): JsonResponse
    {
        try {
            // Lấy thông tin user ở chế độ không lưu trạng thái (stateless) cho API
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            $user = User::with(['admin', 'staff', 'customer'])->where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Tạo tài khoản mới hoàn toàn nếu chưa tồn tại trong hệ thống
                DB::beginTransaction();

                $user = User::create([
                    'fullname'   => $googleUser->getName(),
                    'email'      => $googleUser->getEmail(),
                    'avatar_url' => $googleUser->getAvatar(),
                    'google_id'  => $googleUser->getId(),
                    'is_active'  => true, // Mặc định tin cậy qua Google OAuth2
                ]);

                Customer::create([
                    'customer_id' => $user->user_id,
                ]);

                DB::commit();
            } else {
                // Đồng bộ cập nhật nếu tài khoản đã đăng ký trước đó
                $user->update([
                    'google_id'  => $googleUser->getId(),
                    'avatar_url' => $googleUser->getAvatar(),
                ]);
            }

            // Tạo Sanctum Token cho phiên làm việc mới
            $token = $user->createToken('Google_Auth_Session')->plainTextToken;

            return $this->sendResponse([
                'user'  => new UserResource($user),
                'token' => $token
            ], 'Đăng nhập thông qua Google thành công.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Xác thực tài khoản qua Google thất bại.', [$e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }
}