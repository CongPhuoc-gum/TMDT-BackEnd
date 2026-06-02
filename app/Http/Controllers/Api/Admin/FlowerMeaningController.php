<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\FlowerMeaning;
use App\Http\Requests\Admin\StoreFlowerMeaningRequest;

class FlowerMeaningController extends Controller
{
    /**
     * API Lấy danh sách toàn bộ ý nghĩa các loài hoa (Dành cho bộ lọc ngoài Trang chủ công khai)
     */
    public function index(): JsonResponse
    {
        $meanings = FlowerMeaning::where('is_available', true)->get();
        return response()->json([
            'success' => true,
            'data'    => $meanings
        ]);
    }

    /**
     * API Admin thêm mới ý nghĩa hoa
     */
    public function store(StoreFlowerMeaningRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Mặc định cho phép hiển thị dữ liệu này ngay lập tức
        $data['is_available'] = true;

        $meaning = FlowerMeaning::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Thêm ý nghĩa hoa thành công vào hệ thống.',
            'data'    => $meaning
        ], 201);
    }

    /**
     * API Xem chi tiết ý nghĩa hoa
     */
    public function show(int $id): JsonResponse
    {
        $meaning = FlowerMeaning::find($id);

        if (!$meaning) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy thông tin ý nghĩa hoa.'], 404);
        }

        return response()->json(['success' => true, 'data' => $meaning]);
    }

    /**
     * API Cập nhật thông tin ý nghĩa hoa
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $meaning = FlowerMeaning::find($id);

        if (!$meaning) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy dữ liệu để cập nhật.'], 404);
        }

        $meaning->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật ý nghĩa hoa thành công.',
            'data'    => $meaning
        ]);
    }

    /**
     * API Xóa ý nghĩa hoa ra khỏi hệ thống
     */
    public function destroy(int $id): JsonResponse
    {
        $meaning = FlowerMeaning::find($id);

        if (!$meaning) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy dữ liệu để xóa.'], 404);
        }

        $meaning->delete();
        return response()->json(['success' => true, 'message' => 'Xóa thông tin ý nghĩa hoa thành công.']);
    }
}
