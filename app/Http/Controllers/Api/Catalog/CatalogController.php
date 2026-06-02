<?php

namespace App\Http\Controllers\Api\Catalog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CatalogController extends Controller
{
    /**
     * 1. API công khai: Khách xem danh sách hoa tươi + Lọc theo danh mục (Category)
     * URL: GET /api/products?category_id=2
     */
    public function getProductsForCustomer(Request $request): JsonResponse
    {
        // Gọi truy vấn kèm theo việc nạp sẵn danh sách ảnh (Eager Loading) để tối ưu SQL
        $query = Product::with('images');

        // Nếu khách có chọn lọc theo danh mục hoa cụ thể
        if ($request->has('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        // Lấy danh sách sản phẩm mới nhất
        $products = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $products,
            'message' => 'Lấy danh sách hoa tươi thành công.'
        ], 200);
    }

    /**
     * 2. API Admin: Tạo sản phẩm mới + Upload nhiều ảnh cùng lúc
     * URL: POST /api/admin/products
     */
    public function storeProduct(StoreProductRequest $request): JsonResponse
    {
        // Bước 2.1: Lưu thông tin chữ của sản phẩm vào bảng product trước
        $product = Product::create([
            'category_id'       => $request->input('category_id'),
            'flower_meaning_id' => $request->input('flower_meaning_id'),
            'product_name'      => $request->input('product_name'),
            'description'       => $request->input('description'),
            'price'             => $request->input('price'),
            'stock_quantity'    => $request->input('stock_quantity'),
            'availability'      => $request->input('availability', 1), // Mặc định là 1 (Còn hàng)
        ]);

        // Bước 2.2: Duyệt qua mảng file ảnh gửi lên để upload vào ổ đĩa server
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                // Lưu ảnh vào thư mục công khai 'public/products'
                $path = $imageFile->store('products', 'public');
                $url = Storage::disk('public')->url($path);

                // Lưu link ảnh vào bảng product_image liên kết với product_id vừa tạo
                ProductImage::create([
                    'product_id' => $product->product_id,
                    'image_url'  => $url
                ]);
            }
        }

        // Nạp lại dữ liệu kèm mảng ảnh vừa tạo để trả về phản hồi đầy đủ
        return response()->json([
            'success' => true,
            'data' => $product->load('images'),
            'message' => 'Tạo sản phẩm hoa tươi và upload ảnh thành công.'
        ], 201);
    }

    /**
     * 3. API Admin: Xóa sản phẩm + Tự động dọn sạch file ảnh vật lý trên server
     * URL: DELETE /api/admin/products/{id}
     */
    public function destroyProduct($id): JsonResponse
    {
        $product = Product::with('images')->findOrFail($id);

        // Logic dọn rác server: Duyệt qua từng ảnh để xóa file vật lý trong storage trước
        foreach ($product->images as $img) {
            // Biến đổi link URL thành đường dẫn tương đối trong ổ đĩa để xóa
            $relative減Path = str_replace('/storage/', '', parse_url($img->image_url, PHP_URL_PATH));
            if (Storage::disk('public')->exists($relative減Path)) {
                Storage::disk('public')->delete($relative減Path);
            }
            // Xóa bản ghi ảnh trong DB
            $img->delete();
        }

        // Cuối cùng tiến hành xóa sản phẩm
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm và dọn sạch toàn bộ hình ảnh liên quan khỏi server.'
        ], 200);
    }

    /**
     * 4. API Admin: Cập nhật sản phẩm + Xử lý ảnh mới (nếu có)
     * URL: POST /api/admin/products/{id}
     */
    public function updateProduct(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        // Validate dữ liệu đầu vào (bạn có thể tách ra file Request riêng nếu muốn)
        $request->validate([
            'product_name'   => 'sometimes|required|string|max:150',
            'price'          => 'sometimes|required|numeric|min:0',
            'stock_quantity' => 'sometimes|required|integer|min:0',
            'category_id'    => 'sometimes|required|integer',
        ]);

        // Cập nhật các thông tin chữ cơ bản
        $product->update($request->only([
            'category_id',
            'flower_meaning_id',
            'product_name',
            'description',
            'price',
            'discount_price',
            'stock_quantity',
            'size',
            'availability'
        ]));

        // Nếu Admin có chọn thêm/thay ảnh mới lên
        if ($request->hasFile('images')) {
            // Tùy chọn của nhóm: Nếu muốn xóa Sạch ảnh cũ khi up ảnh mới, mở comment dòng dưới:
            // $this->clearOldImages($product);

            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('products', 'public');
                $url = Storage::disk('public')->url($path);

                ProductImage::create([
                    'product_id' => $product->product_id,
                    'image_url'  => $url
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $product->load('images'),
            'message' => 'Cập nhật thông tin sản phẩm hoa tươi thành công.'
        ], 200);
    }

    /**
     * Hàm phụ trợ dọn ảnh cũ của sản phẩm (nếu nhóm muốn xóa sạch ảnh cũ khi cập nhật ảnh mới)
     */
    private function clearOldImages($product)
    {
        foreach ($product->images as $img) {
            $relative減Path = str_replace('/storage/', '', parse_url($img->image_url, PHP_URL_PATH));
            if (Storage::disk('public')->exists($relative減Path)) {
                Storage::disk('public')->delete($relative減Path);
            }
            $img->delete();
        }
    }

    public function storeCategory(\App\Http\Requests\Admin\StoreCategoryRequest $request): JsonResponse
    {
        $category = \App\Models\Category::create([
            'category_name' => $request->input('category_name'),
            'description'   => $request->input('description'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm mới danh mục hoa thành công.',
            'data'    => $category
        ], 201);
    }

    /**
     * 5. API Admin: Cập nhật danh mục hoa
     * URL: PUT /api/admin/categories/{id}
     */
    public function updateCategory(Request $request, $id): JsonResponse
    {
        $category = \App\Models\Category::findOrFail($id);

        $request->validate([
            'category_name' => 'required|string|max:100',
            'description'   => 'nullable|string'
        ]);

        $category->update($request->only(['category_name', 'description']));

        return response()->json([
            'success' => true,
            'data' => $category,
            'message' => 'Cập nhật danh mục hoa thành công.'
        ], 200);
    }

    /**
     * 6. API Admin: Xóa danh mục hoa
     * URL: DELETE /api/admin/categories/{id}
     */
    public function destroyCategory($id): JsonResponse
    {
        $category = \App\Models\Category::findOrFail($id);
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa danh mục hoa thành công.'
        ], 200);
    }
}