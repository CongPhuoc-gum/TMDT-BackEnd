<?php

namespace App\Http\Controllers\Api\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CheckoutRequest;
use App\Http\Requests\Admin\PlaceOrderRequest;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CheckoutController extends Controller
{
    /**
     * API Tính toán thử hóa đơn (Checkout Xem trước)
     */
    public function calculateOrder(CheckoutRequest $request): JsonResponse
    {
        $items = $request->input('items');
        $couponCode = $request->input('coupon_code');
        $shippingFee = $request->input('shipping_fee', 0);

        $totalProductAmount = 0;

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $price = $product->discount_price ?? $product->price;
                $totalProductAmount += $price * $item['quantity'];
            }
        }

        $discountAmount = 0;
        $shippingDiscountAmount = 0;
        $couponError = null;
        $coupon = null;

        if ($couponCode) {
            $coupon = Coupon::where('coupon_code', $couponCode)->first();
            $now = Carbon::now();

            if (!$coupon || $coupon->is_active !== 1) {
                $couponError = "Mã giảm giá không tồn tại hoặc đã bị khóa.";
            } elseif ($now->lt(Carbon::parse($coupon->start_date)) || $now->gt(Carbon::parse($coupon->end_date))) {
                $couponError = "Mã giảm giá đã hết hạn sử dụng.";
            } elseif ($coupon->current_usage >= $coupon->usage_limit) {
                $couponError = "Mã giảm giá đã hết lượt sử dụng.";
            } elseif ($totalProductAmount < $coupon->min_purchase_amount) {
                $couponError = "Đơn hàng chưa đạt giá trị tối thiểu " . number_format($coupon->min_purchase_amount) . "đ để áp dụng.";
            } else {
                if ($coupon->discount_type === 'Percentage') {
                    $discountAmount = $totalProductAmount * ($coupon->discount_value / 100);
                    if ($coupon->max_discount_amount && $discountAmount > $coupon->max_discount_amount) {
                        $discountAmount = $coupon->max_discount_amount;
                    }
                } elseif ($coupon->discount_type === 'FixedAmount') {
                    if (str_contains(strtolower($coupon->coupon_code), 'ship')) {
                        $shippingDiscountAmount = min($coupon->discount_value, $shippingFee);
                    } else {
                        $discountAmount = min($coupon->discount_value, $totalProductAmount);
                    }
                }
            }
        }

        $finalShippingFee = max(0, $shippingFee - $shippingDiscountAmount);
        $finalPayment = $totalProductAmount + $finalShippingFee - $discountAmount;

        return response()->json([
            'success' => true,
            'message' => $couponError ? "Tính toán đơn hàng hoàn tất (Lưu ý: $couponError)" : "Áp dụng mã giảm giá thành công.",
            'data' => [
                'total_product_amount'  => $totalProductAmount,
                'original_shipping_fee' => $shippingFee,
                'coupon_code_applied'   => $couponError ? null : $couponCode,
                'discount_amount'       => $discountAmount,
                'shipping_discount'     => $shippingDiscountAmount,
                'final_shipping_fee'    => $finalShippingFee,
                'final_payment'         => max(0, $finalPayment),
                'coupon_error'          => $couponError
            ]
        ], 200);
    }

    /**
     * API Đặt hàng chính thức + Chia nhánh số tiền cọc COD 30% / Online 100%
     */
    public function placeOrder(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product,product_id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_fee' => 'required|numeric|min:0',
            'coupon_code' => 'nullable|string',
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'payment_method' => 'required|in:VNPAY,STRIPE,COD',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $items = $request->input('items');
            $couponCode = $request->input('coupon_code');
            $paymentMethod = $request->input('payment_method');

            $totalProductAmount = 0;
            $productDetails = [];

            // 2. Tính tiền hoa gốc
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                
                $price = $product->discount_price ?? $product->price; 
                $subtotal = $price * $item['quantity'];
                $totalProductAmount += $subtotal;

                $productDetails[] = [
                    'product_id'   => $product->product_id,
                    'product_name' => $product->product_name,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $price,
                    'subtotal'     => $subtotal
                ];
            }

            // 3. Xử lý mã giảm giá Coupon
            $discountAmount = 0;
            $couponId = null;
            if ($couponCode) {
                $coupon = DB::table('coupon')
                    ->where('coupon_code', $couponCode)
                    ->where('is_active', true)
                    ->where('start_date', '<=', date('Y-m-d'))
                    ->where('end_date', '>=', date('Y-m-d'))
                    ->first();

                if ($coupon && $totalProductAmount >= $coupon->min_purchase_amount) {
                    $couponId = $coupon->coupon_id;
                    if ($coupon->discount_type === 'Percentage') {
                        $discountAmount = ($totalProductAmount * $coupon->discount_value) / 100;
                        if ($coupon->max_discount_amount) {
                            $discountAmount = min($discountAmount, $coupon->max_discount_amount);
                        }
                    } else {
                        $discountAmount = $coupon->discount_value;
                    }
                }
            }

            $shippingFee = $request->input('shipping_fee', 0);

            // 4. Bắt Token động qua Guard Sanctum bảo mật
            $user = request()->user('sanctum');
            $customerId = $user ? $user->user_id : 1;

            // 5. Khởi tạo bản ghi Đơn hàng
            $order = new Order();
            $order->customer_id = $customerId;
            $order->coupon_id = $couponId;
            $order->order_date = date('Y-m-d H:i:s');
            $order->delivery_date = date('Y-m-d', strtotime('+1 day')); 
            $order->total_price = $totalProductAmount;
            $order->discount_amount = $discountAmount;
            $order->distance_km = 0.00;
            $order->shipping_fee = $shippingFee;
            
            $order->status = 'Pending';
            $order->delivery_address = $request->input('shipping_address');
            $order->special_instructions = $request->input('notes');
            $order->payment_method = $paymentMethod;
            $order->paid = false;
            
            $order->save();

            // 6. Lưu chi tiết sản phẩm đơn hàng vào bảng order_item
            foreach ($productDetails as $detail) {
                OrderItem::create([
                    'order_id'   => $order->order_id,
                    'product_id' => $detail['product_id'],
                    'quantity'   => $detail['quantity'],
                    'unit_price' => $detail['unit_price'],
                    'subtotal'   => $detail['subtotal']
                ]);
            }

            // 7. Sinh link thanh toán dựa vào phương thức người dùng chọn
            $paymentUrl = null;
            $finalPriceToPay = $totalProductAmount - $discountAmount + $shippingFee;

            if ($paymentMethod === 'COD') {
                // Đơn COD: Ép khách cọc trước 30% tiền đơn hàng qua VNPAY
                $depositAmount = $finalPriceToPay * 0.3;
                $paymentUrl = $this->createVnpayUrl($order, $depositAmount, 'COD');
            } elseif ($paymentMethod === 'VNPAY') {
                // Trả thẳng 100% qua VNPAY
                $paymentUrl = $this->createVnpayUrl($order, $finalPriceToPay, 'VNPAY');
            } elseif ($paymentMethod === 'STRIPE') {
                // Trả thẳng 100% qua Stripe
                $paymentUrl = $this->createStripeUrl($order, $productDetails);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công! Vui lòng hoàn tất thanh toán.',
                'data' => [
                    'order_id'    => $order->order_id,
                    'total_price' => $finalPriceToPay,
                    'payment_url' => $paymentUrl
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo đơn hàng.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

private function createVnpayUrl($order, $amountToPay, $prefixType)
    {
        // Lấy thông tin cấu hình bảo mật trực tiếp từ file .env
        $vnp_Url = env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $vnp_TmnCode = env('VNP_TMN_CODE'); 
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_Returnurl = env('VNP_RETURN_URL'); 

        // Ghép chuỗi theo logic hàm callback để phân tích mã đơn hàng (Ví dụ: VNPAY_12 hoặc COD_12)
        $vnp_TxnRef = $prefixType . "_" . $order->order_id; 
        $vnp_OrderInfo = "Thanh toan don hang #" . $order->order_id;
        $vnp_OrderType = "billpayment";
        
        // Chuẩn hóa tiền tệ: Nhân 100 và loại bỏ hoàn toàn ký tự thập phân lạ
        $vnp_Amount = intval(round($amountToPay)) * 100; 
        
        $vnp_Locale = 'vn';
        $vnp_IpAddr = request()->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay", // BẮT BUỘC có tham số này
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        // Sắp xếp mảng theo thứ tự ABC của Key trước khi băm bảo mật
        ksort($inputData);
        
        $query = "";
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            $hashdata .= urlencode($key) . "=" . urlencode($value) . "&";
            $query .= urlencode($key) . "=" . urlencode($value) . "&";
        }
        
        $hashdata = rtrim($hashdata, '&');
        $query = rtrim($query, '&');

        // Tạo chữ ký mã hóa SHA512 khớp hoàn toàn dữ liệu
        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        
        return $vnp_Url . "?" . $query . '&vnp_SecureHash=' . $vnpSecureHash;
    }
    /**
     * Khởi tạo Session Checkout Stripe chi tiết từng sản phẩm
     */
    private function createStripeUrl($order, $productDetails)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $lineItems = [];
        foreach ($productDetails as $product) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'vnd',
                    'product_data' => ['name' => $product['product_name']],
                    'unit_amount' => (int)$product['unit_price'],
                ],
                'quantity' => $product['quantity'],
            ];
        }

        if ($order->shipping_fee > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'vnd',
                    'product_data' => ['name' => 'Phí vận chuyển hoa tươi Cyberbloom'],
                    'unit_amount' => (int)$order->shipping_fee,
                ],
                'quantity' => 1,
            ];
        }

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'  => $lineItems,
            'mode'        => 'payment',
            'success_url' => env('STRIPE_RETURN_URL') . '?session_id={CHECKOUT_SESSION_ID}&order_id=' . $order->order_id,
            'cancel_url'  => env('STRIPE_RETURN_URL') . '?status=cancel&order_id=' . $order->order_id,
        ]);

        return $session->url;
    }

    /**
     * Nhận tín hiệu xử lý kết quả IPN/Callback từ phía VNPAY
     */
    public function vnpayCallback(Request $request): JsonResponse
    {
        $vnp_ResponseCode = $request->get('vnp_ResponseCode');
        $txnRef = $request->get('vnp_TxnRef');

        // Tách chuỗi txnRef để tìm ra ID đơn hàng gốc dựa vào dấu gạch dưới
        $parts = explode('_', $txnRef);
        $orderId = $parts[1] ?? null;

        if (!$orderId) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy mã đơn hàng.'], 400);
        }

        $order = Order::findOrFail($orderId);

        if ($vnp_ResponseCode === '00') {
            // Cập nhật trạng thái đơn hàng sang Đang xử lý và Đã thanh toán
            $order->update(['status' => 'Processing', 'paid' => true]); 
            return response()->json(['success' => true, 'message' => 'Giao dịch qua VNPAY hoàn tất!', 'order_id' => $orderId]);
        }

        $order->update(['status' => 'Cancelled']);
        return response()->json(['success' => false, 'message' => 'Giao dịch VNPAY thất bại.'], 400);
    }

    /**
     * Nhận tín hiệu xử lý kết quả Callback từ phía Stripe
     */
    public function stripeCallback(Request $request): JsonResponse
    {
        $orderId = $request->get('order_id');
        $sessionId = $request->get('session_id');

        $order = Order::findOrFail($orderId);

        if ($request->get('status') === 'cancel' || !$sessionId) {
            $order->update(['status' => 'Cancelled']);
            return response()->json(['success' => false, 'message' => 'Hủy thanh toán thẻ Stripe.'], 400);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = StripeSession::retrieve($sessionId);

        if ($session->payment_status === 'paid') {
            $order->update(['status' => 'Processing', 'paid' => true]);
            return response()->json(['success' => true, 'message' => 'Thanh toán thẻ quốc tế thành công!', 'order_id' => $orderId]);
        }

        return response()->json(['success' => false, 'message' => 'Thanh toán chưa hoàn thành.'], 400);
    }
}