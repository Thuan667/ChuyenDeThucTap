<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProduct;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
class ProductController extends Controller
{
    public function index()
    {
        return Product::with("category", "stocks")->paginate(5);
    }

    public function show($id)
    {
        $product = Product::with("category", "stocks")->findOrFail($id);
        if ($product->reviews()->exists()) {
            $product['review'] = $product->reviews()->avg('rating');
            $product['num_reviews'] = $product->reviews()->count();
        }
        return $product;
    }


    public function store(Request $request)
    {
        // Validate các dữ liệu đầu vào
        $request->validate([
            'name' => 'required',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required',
            'description' => 'required',
            'details' => 'required',
            'price' => 'required|numeric',
            'photo' => 'required|array',
            'photo.*' => 'string',
        ]);

        try {
            // Mảng để lưu tên của ảnh
            $photoNames = $request->input('photo'); // Giả định rằng tên file được gửi dưới dạng mảng

            // Chỉ lấy ảnh đầu tiên trong mảng
            $photoName = $photoNames[0];

            // Đường dẫn tới thư mục img trong frontend
            $path = 'D:\CHUYENDETHUCTAP\BaoCaoChyenDe TT\BaoCaoChyenDe TT\frontend\src\assets\img';

            // Kiểm tra nếu thư mục không tồn tại, tạo nó
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }

            // Đường dẫn nguồn
            $sourcePath = "D:/img/{$photoName}";

            // Đường dẫn đích
            $destinationPath = $path . '/' . $photoName;

            // Kiểm tra nếu ảnh đã tồn tại
            if (!file_exists($destinationPath)) {
                if (file_exists($sourcePath)) {
                    // Di chuyển hoặc sao chép ảnh vào thư mục đích
                    copy($sourcePath, $destinationPath);
                }
            } else {
                // Log hoặc xử lý khi file đã tồn tại
                \Log::info("File {$photoName} đã tồn tại, không cần sao chép.");
            }

            // Lưu sản phẩm, lưu tên ảnh như một chuỗi đơn giản
            $product = Product::create([
                'user_id' => $request->user_id ?? 1,
                'deal_id' => $request->deal_id ?? null,
                'category_id' => $request->category_id,
                'brand' => $request->brand,
                'name' => $request->name,
                'description' => $request->description,
                'details' => $request->details,
                'price' => $request->price,
                'photo' => $photoName, // Lưu tên ảnh như một chuỗi đơn giản
            ]);

            return response()->json([
                'message' => 'Thêm sản phẩm thành công!',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            \Log::error('Product creation failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Product creation failed!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand' => 'required|string|max:255',
            'description' => 'required|string',
            'details' => 'required|string',
            'price' => 'required|numeric',
            'photo' => 'required|string', // Chỉ nhận một tên ảnh
        ]);

        $photoName = $request->input('photo'); // Nhận tên ảnh đơn
        $path = 'D:\CHUYENDETHUCTAP\BaoCaoChyenDe TT\BaoCaoChyenDe TT\frontend\src\assets\img';

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $sourcePath = "D:/img/{$photoName}";
        $destinationPath = $path . '/' . $photoName;

        // Chỉ sao chép nếu ảnh không tồn tại ở đích và tồn tại ở nguồn
        if (!file_exists($destinationPath) && file_exists($sourcePath)) {
            copy($sourcePath, $destinationPath);
        }

        // Lưu tên ảnh vào cơ sở dữ liệu dưới dạng chuỗi
        $product->photo = $photoName; // Chỉ lưu tên ảnh
        $product->update($validatedData);

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }










    public function destroy($id)
    {
        try {
            // Xác thực người dùng
            if ($user = JWTAuth::parseToken()->authenticate()) {
                // Tìm sản phẩm theo ID
                $product = Product::findOrFail($id);

                // Kiểm tra nếu sản phẩm có ảnh
                if ($product->photo) {
                    // Đường dẫn tới thư mục img trong frontend
                    $path = 'D:\CHUYENDETHUCTAP\BaoCaoChyenDe TT\BaoCaoChyenDe TT\frontend\src\assets\img';
                    $photoPath = $path . '/' . $product->photo;

                    // Kiểm tra xem tệp có tồn tại không
                    if (file_exists($photoPath)) {
                        // Xóa ảnh nếu tồn tại
                        unlink($photoPath);
                    } else {
                        \Log::warning("File không tìm thấy: {$photoPath}");
                    }
                }

                // Xóa sản phẩm
                $product->delete();

                return response()->json(['message' => 'Sản phẩm đã được xóa thành công!']);
            } else {
                return response()->json(['message' => 'Người dùng chưa xác thực'], 401);
            }
        } catch (\Exception $e) {
            \Log::error('Xóa sản phẩm không thành công: ' . $e->getMessage());
            return response()->json(['message' => 'Xóa sản phẩm không thành công', 'error' => $e->getMessage()], 500);
        }
    }







    public function search(Request $request)
    {
        \Log::info('Search method called');

        $query = $request->input('query');
        \Log::info('Searching for: ' . $query);

        // Tìm kiếm chỉ theo tên sản phẩm
        $product = Product::where('name', 'like', "%$query%")->get();

        \Log::info('Products found: ' . $product->count());

        return response()->json($product);
    }


}
