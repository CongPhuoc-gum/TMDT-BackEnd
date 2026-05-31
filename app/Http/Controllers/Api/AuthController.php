<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\UserResource;
use App\Mail\OtpVerificationMail;
use App\Models\User;
use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\ForgotPasswordMail;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    /**
     * Đăng ký tài khoản dành cho Khách hàng mua hoa
     */
    public function register(Request $request): JsonResponse
    {
        // Đã sửa 'unique:users,email' thành 'unique:user,email' để trỏ đúng bảng của bạn
        $validated = $request->validate([
            'fullname' => 'required|string|max:100',
            'email'    => 'required|string|email|max:100|unique:user,email', 
            'password' => 'required|string|min:8',
            'phone'    => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // Tạo bản ghi lưu thông tin đăng ký vào bảng user
            $user = User::create([
                'fullname' => $validated['fullname'],
                'email'    => $validated['email'],
                'password_hash' => Hash::make($validated['password']), // Khớp chuẩn cột password_hash trong DB
                'phone'    => $validated['phone'],
                'is_active' => false, // Chờ bước xác thực mã OTP kích hoạt
            ]);

            // Đồng bộ tạo bản ghi tương ứng bên bảng khách hàng (customer)
            Customer::create([
                'customer_id' => $user->user_id,
            ]);

            // Sinh mã số OTP ngẫu nhiên gồm 6 chữ số
            $otp = (string) rand(100000, 999999);
            
            // Lưu trữ OTP vào bộ nhớ đệm Cache với thời hạn hiệu lực 10 phút
            Cache::put('otp_verification_' . $user->email, $otp, now()->addMinutes(10));

            // Tiến hành gửi Email HTML nghệ thuật chứa mã kích hoạt cho người dùng qua Mailtrap
            Mail::to($user->email)->send(new OtpVerificationMail($otp));

            DB::commit();

            return $this->sendResponse(
                ['email' => $user->email],
                'Đăng ký tài khoản thành công! Hệ thống đã gửi một mã OTP kích hoạt đến Email của bạn.',
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Lỗi hệ thống khi xử lý đăng ký tài khoản.', [$e->getMessage()], 500);
        }
    }

    /**
     * Xác thực OTP để kích hoạt tài khoản
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:user,email',
            'otp'   => 'required|string|size:6',
        ]);

        $cachedOtp = Cache::get('otp_verification_' . $validated['email']);

        if (!$cachedOtp || $cachedOtp !== $validated['otp']) {
            return $this->sendError('Mã xác thực OTP không chính xác hoặc đã hết hạn sử dụng.', [], Response::HTTP_BAD_REQUEST);
        }

        // Kích hoạt tài khoản người dùng trực tiếp
        $user = User::where('email', $validated['email'])->first();
        $user->is_active = true;
        $user->save();

        // Giải phóng mã OTP khỏi Cache ngay sau khi hoàn tất xác thực thành công
        Cache::forget('otp_verification_' . $validated['email']);

        return $this->sendResponse(new UserResource($user), 'Tài khoản của bạn đã được kích hoạt thành công. Hiện tại bạn đã có thể đăng nhập.');
    }

    /**
     * Đăng nhập chuẩn vào hệ thống Cyberbloom
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Eager load toàn bộ quan hệ vai trò để hạn chế tối đa lỗi truy vấn N+1
        $user = User::with(['admin', 'staff', 'customer'])->where('email', $validated['email'])->first();

        // So khớp mật khẩu với trường password_hash thiết kế trong DB
        if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
            return $this->sendError('Thông tin tài khoản hoặc mật khẩu không chính xác.', [], Response::HTTP_UNAUTHORIZED);
        }

        // Chặn đứng hành vi đăng nhập nếu tài khoản chưa được kích hoạt OTP
        if (!$user->is_active) {
            return $this->sendError('Tài khoản chưa được kích hoạt. Vui lòng xác thực mã OTP đã gửi qua email.', [], Response::HTTP_FORBIDDEN);
        }

        // Tạo chuỗi Sanctum Token quản lý phiên làm việc API
        $token = $user->createToken($request->input('device_name', 'Cyberbloom_Platform'))->plainTextToken;

        return $this->sendResponse([
            'user'  => new UserResource($user),
            'token' => $token
        ], 'Xác thực thông tin tài khoản và đăng nhập thành công.');
    }
    /**
     * 1. Gửi mã OTP quên mật khẩu
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email|exists:user,email',
        ], [
            'email.exists' => 'Email này không tồn tại trên hệ thống của chúng tôi.'
        ]);

        try {
            $otp = (string) rand(100000, 999999);
            
            // Lưu OTP quên mật khẩu vào Cache trong 10 phút
            Cache::put('password_otp_' . $request->email, $otp, now()->addMinutes(10));

            // Gửi mail bằng class Mail mới tạo
            Mail::to($request->email)->send(new ForgotPasswordMail($otp));

            return $this->sendResponse([], 'Mã OTP đặt lại mật khẩu đã được gửi đến email của bạn.');
        } catch (\Exception $e) {
            return $this->sendError('Lỗi hệ thống khi gửi mã OTP.', [$e->getMessage()], 500);
        }
    }

    /**
     * 2. Xác thực OTP quên mật khẩu để lấy mã Token thiết lập lại
     */
    public function verifyPasswordOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:user,email',
            'otp'   => 'required|string|size:6',
        ]);

        $cachedOtp = Cache::get('password_otp_' . $request->email);

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return $this->sendError('Mã OTP không chính xác hoặc đã hết hạn.', [], Response::HTTP_BAD_REQUEST);
        }

        // Tạo chuỗi Token ngẫu nhiên tạm thời để bảo mật luồng đổi mật khẩu (hạn 5 phút)
        $resetToken = Str::random(40);
        Cache::put('password_reset_token_' . $request->email, $resetToken, now()->addMinutes(5));
        
        // Xóa OTP cũ để không dùng lại được nữa
        Cache::forget('password_otp_' . $request->email);

        return $this->sendResponse([
            'email' => $request->email,
            'reset_token' => $resetToken
        ], 'Xác thực OTP thành công. Vui lòng thiết lập mật khẩu mới.');
    }

    /**
     * 3. Thực hiện cập nhật đổi mật khẩu mới vào Database
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'       => 'required|email|exists:user,email',
            'reset_token' => 'required|string',
            'password'    => 'required|string|min:8|confirmed', // 'confirmed' bắt buộc truyền thêm trường 'password_confirmation' ở Postman
        ], [
            'password.confirmed' => 'Xác nhận mật khẩu mới không trùng khớp.'
        ]);

        $cachedToken = Cache::get('password_reset_token_' . $request->email);

        if (!$cachedToken || $cachedToken !== $request->reset_token) {
            return $this->sendError('Mã Token xác thực đổi mật khẩu không hợp lệ hoặc đã hết hạn.', [], Response::HTTP_BAD_REQUEST);
        }

        // Tiến hành cập nhật mật khẩu mới vào bảng user
        $user = User::where('email', $request->email)->first();
        $user->password_hash = Hash::make($request->password);
        $user->save();

        // Xóa Token khôi phục mật khẩu khỏi cache sau khi đổi thành công
        Cache::forget('password_reset_token_' . $request->email);

        return $this->sendResponse([], 'Mật khẩu của bạn đã được thay đổi thành công. Bạn đã có thể đăng nhập lại.');
    }
}