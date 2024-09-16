<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductAttributeValue;
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

    public function AdminProducts($admin_id,$category_id)
    {
        $products = Product::with('category','attributes','user')->where('user_id', $admin_id)->where('category_id', $category_id)->get();
        return response()->json($products);
    }

    public function AllAdminProducts($admin_id)
    {
        $products = Product::with('user')->where('user_id', $admin_id)->get();
        return response()->json($products);
    }

    // Show a single product
    public function show($product_id)
{
    $product = Product::with('category', 'attributes','attributes.attribute','attributes.value' ,'user')->findOrFail($product_id);
    return response()->json($product);
}


    // Create a new product
    public function store(Request $request)
    {

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'brand' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'category_id' => 'required|exists:categories,_id',
            'user_id' => 'required|exists:users,_id',
            'attributes' => 'required|array', // Validate attributes array
            'attributes.*.attribute_id' => 'required|exists:attributes,_id', // Ensure each attribute exists
            'attributes.*.value_id' => 'required|exists:values,_id', // Ensure each value exists
            'attributes.*.stock' => 'required|integer', // Stock per attribute
            'attributes.*.unit_price' => 'required|numeric', // Price per attribute
        ]);
        $attributes=$validated['attributes'];
        // Handle the image upload if provided
        $imageName = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/img/products', $imageName);
        }

        // Create the product
        $product = new Product([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'brand' => $validated['brand'],
            'image' => $imageName,
            'category_id' => $validated['category_id'],
            'user_id' => $validated['user_id'],
        ]);

        $product->save();

        foreach ($attributes as $attribute) {
            ProductAttributeValue::create([
                'product_id' => $product->id, // Use the ID of the newly created product
                'attribute_id' => $attribute['attribute_id'],
                'value_id' => $attribute['value_id'],
                'stock' => $attribute['stock'],
                'unit_price' => $attribute['unit_price'],
            ]);
        }



        // Return the created product with its attributes
        return response()->json($product, 201);
    }



    // Update an existing product
    public function update(Request $request, $product_id)
{
    // Validate the request
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
        'brand' => 'nullable|string|max:255',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'category_id' => 'required|exists:categories,_id',
        'user_id' => 'required|exists:users,_id',
        'attributes' => 'required|array', // Validate attributes array
        'attributes.*.attribute_id' => 'required|exists:attributes,_id', // Ensure each attribute exists
        'attributes.*.value_id' => 'required|exists:values,_id', // Ensure each value exists
        'attributes.*.stock' => 'required|integer', // Stock per attribute
        'attributes.*.unit_price' => 'required|numeric', // Price per attribute
    ]);

    // Find the existing product
    $product = Product::findOrFail($product_id);

    // Handle the image upload if provided
    if ($request->hasFile('image')) {
        // Delete the old image if it exists
        if ($product->image) {
            Storage::delete('public/img/products/' . $product->image);
        }

        // Store the new image
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/img/products', $imageName);
    } else {
        // Keep the old image name if no new image is uploaded
        $imageName = $product->image;
    }

    // Update the product details
    $product->update([
        'name' => $validated['name'],
        'description' => $validated['description'],
        'price' => $validated['price'],
        'stock' => $validated['stock'],
        'brand' => $validated['brand'],
        'image' => $imageName,
        'category_id' => $validated['category_id'],
        'user_id' => $validated['user_id'],
    ]);

    // Update or create attributes
    ProductAttributeValue::where('product_id', $product->id)->delete(); // Remove old attributes

    foreach ($validated['attributes'] as $attribute) {
        ProductAttributeValue::create([
            'product_id' => $product->id,
            'attribute_id' => $attribute['attribute_id'],
            'value_id' => $attribute['value_id'],
            'stock' => $attribute['stock'],
            'unit_price' => $attribute['unit_price'],
        ]);
    }

    // Return the updated product with its attributes
    return response()->json($product, 200);
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
