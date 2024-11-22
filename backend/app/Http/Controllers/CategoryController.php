<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }
    public function show($id)
    {
    $category = Category::find($id);

    if (!$category) {
        return response()->json(['message' => 'Category not found'], 404);
    }

    return response()->json($category);
}

    public function new($id)
    {
        $products = Product::with('category')->where('category_id', $id)->orderBy('id', 'desc')->take(4)->get();
        foreach ($products as $product) {
            if ($product->reviews()->exists()) {
                $product['review'] = $product->reviews()->avg('rating');
            }
        }
        return $products;
    }
    public function newPage($id)
    {
        $perpage = 4;
        $products = Product::with('category')->where('category_id', $id)->orderBy('id', 'desc')->paginate($perpage);
        $products->each(function ($product) {
            if ($product->reviews()->exists()) {
                $product['review'] = $product->reviews()->avg('rating');
            }
        });
        return $products;
    }

    public function topSelling($id)
    {
        $products = Product::with('category')->where('category_id', $id)->take(6)->get();
        foreach ($products as $product) {
            if ($product->reviews()->exists()) {
                $product['review'] = $product->reviews()->avg('rating');
            }

            if ($product->stocks()->exists()) {
                $num_orders = 0;
                $stocks = $product->stocks()->get();
                foreach ($stocks as $stock) {
                    $num_orders += $stock->orders()->count();
                }
                $product['num_orders'] = $num_orders;
            } else {
                $product['num_orders'] = 0;
            }
        }
        return $products->sortByDesc('num_orders')->values()->all();
    }

public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::create([
            'name' => $validatedData['name'],
        ]);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'message' => 'Category not found',
            ], 404);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ], 200);
    }



    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category->name = $validatedData['name'];
        $category->save();

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category,
        ]);
    }



}
