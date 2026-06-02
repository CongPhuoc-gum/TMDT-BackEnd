<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomRequest;
use App\Models\CustomRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CustomRequestController extends Controller
{
    public function indexForCustomer(): JsonResponse
    {
        $requests = CustomRequest::where('customer_id', Auth::id())->get();
        return response()->json(['success' => true, 'data' => $requests]);
    }

    public function store(StoreCustomRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['customer_id'] = Auth::id();
        $data['status'] = 'Pending';

        // Xử lý upload ảnh mẫu khách gửi nếu có
        if ($request->hasFile('reference_image_url')) {
            $path = $request->file('reference_image_url')->store('uploads/custom_designs', 'public');
            $data['reference_image_url'] = $path;
        }

        $customRequest = CustomRequest::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu thiết kế riêng của bạn đã được gửi tới Designer.',
            'data' => $customRequest
        ], 201);
    }
}
