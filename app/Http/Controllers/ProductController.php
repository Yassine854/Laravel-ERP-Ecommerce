<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    // List all products
    public function index($category_id)
    {
        $products = Product::with('category', 'user')->where('category_id', $category_id)->get();
        return response()->json($products);
    }

    // Show a single product
    public function show($id)
    {
        $product = Product::with('category', 'user')->findOrFail($id);
        return response()->json($product);
    }

    // Create a new product
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
        'brand' => 'nullable|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'category_id' => 'required|exists:categories,_id',
        'user_id' => 'required|exists:users,_id',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $imageName=null;
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/img/products', $imageName);
    }


    $product = new Product([
        'name' => $request->name,
        'description' => $request->description,
        'price' => $request->price,
        'stock' => $request->stock,
        'brand' => $request->brand,
        'image' => $imageName,
        'category_id' => $request->category_id,
        'user_id' => $request->user_id,
    ]);

    $product->save();
    return response()->json($product, 201);
}


    // Update an existing product
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => 'sometimes|exists:categories,_id',
            'brand' => 'nullable|string|max:255',
            'stock' => 'required|integer',
            'user_id' => 'sometimes|exists:users,_id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->hasFile('image')) {
            if ($product->image && Storage::exists('public/img/products/' . $product->image)) {
                Storage::delete('public/img/products/' . $product->image);
            }
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/img/products', $imageName);
            $product->image=$imageName;
        }

        if ($request->has('name')) {
            $product->name = $request->name;
        }
        if ($request->has('description')) {
            $product->description = $request->description;
        }
        if ($request->has('price')) {
            $product->price = $request->price;
        }
        if ($request->has('stock')) {
            $product->stock = $request->stock;
        }
        if ($request->has('brand')) {
            $product->brand = $request->brand;
        }
        if ($request->has('category_id')) {
            $product->category_id = $request->category_id;
        }
        if ($request->has('user_id')) {
            $product->user_id = $request->user_id;
        }

        $product->save();
        return response()->json($product);
    }

    // Delete a product
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        if ($product->image!=null)
            Storage::delete('public/img/products/' . $product->image);
        $product->delete();
        return response()->json(null, 204);
    }
}
