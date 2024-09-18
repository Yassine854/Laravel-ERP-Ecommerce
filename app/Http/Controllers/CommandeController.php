<?php

namespace App\Http\Controllers;


use App\Models\Product;
use App\Models\Commande;
use Illuminate\Http\Request;
use App\Models\CommandProduct;
use App\Models\CommandeProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductAttributeValue;

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $commandes = Commande::with('admin','user','products')->where('admin_id',$id)->get();
        return response()->json($commandes);
    }


    public function getAttributesWithValuesForProduct($productId)
    {
        // Fetch attributes and their values for the given product ID
        $attributesWithValues = ProductAttributeValue::where('product_id', $productId)
            ->with('attribute', 'value') // Ensure relationships are loaded
            ->get(); // Added semicolon here

        // Return the attributes with values as JSON response
        return response()->json($attributesWithValues);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

     public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'client_id' => 'required|exists:users,_id',
            'total_amount' => 'required|numeric',
            'products' => 'required|array',
            'products.*._id' => 'required|exists:products,_id',
            'products.*.attributes' => 'required',
            'products.*.attributes.*.attributes' => 'required',
            'products.*.attributes.*.quantity' => 'required|integer|min:1',
        ]);

        // Start a transaction to ensure data integrity

            // Create the Commande
            $commande = Commande::create([
                'admin_id' =>$request->admin_id,
                'user_id' => $validated['client_id'],
                'total_amount' => $validated['total_amount'],
                'status' => 'en attente',
            ]);

            $products = $validated['products'];
            foreach ($products as $product) {
                $p = Product::findOrFail($product['_id']);
                foreach ($product['attributes'] as $attributeGroup) {
                    foreach ($attributeGroup['attributes'] as $attribute) {
                        // Create CommandProduct entries for each product with its attributes
                        CommandProduct::create([
                            'commande_id' => $commande->id,
                            'product_id' => $product['_id'],
                            'price' => $p->price,
                            'quantity'=>$attributeGroup['quantity'],
                            'attribute_id' => $attribute['attribute_id'],
                            'value_id' => $attribute['value_id'],
                        ]);
                    }
                }
                // Update product stock
                $p->save();
            }
            return response()->json(['message' => 'Commande created successfully!', 'commande' => $commande], 201);

    }


    /**
     * Display the specified resource.
     */
    public function show($id)
{
    // Eager load 'admin', 'user', 'products', and 'products.product'
    $commande = Commande::with(['admin', 'user', 'products.product'])->findOrFail($id);

    // Transform the response to include product details in each product entry
    $commande->products = $commande->products->map(function ($commandProduct) {
        return [
            'product_id' => $commandProduct->product_id,
            'quantity' => $commandProduct->quantity,
            'price' => $commandProduct->price,
            'product' => [
                'name' => $commandProduct->product->name,
                'stock' => $commandProduct->product->stock,
                'image' => $commandProduct->product->image,
            ],
        ];
    });

    return response()->json($commande);
}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Commande $commande)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    // Validate the request
    $validated = $request->validate([
        'client_id' => 'required|exists:users,_id',
        'total_amount' => 'required|numeric',
        'products' => 'required|array',
        'products.*._id' => 'required|exists:products,_id',
        'products.*.quantity' => 'required|integer|min:1',
    ]);

    // Start a transaction to ensure data integrity

    try {
        // Find the Commande to update
        $commande = Commande::findOrFail($id);
        $oldProducts = CommandProduct::where('commande_id', $commande->id)->get();

        // Update the Commande
        $commande->update([
            'user_id' => $validated['client_id'],
            'total_amount' => $validated['total_amount'],
            'status' => 'en attente',
        ]);

        // Update the products and adjust the stock
        $products = $validated['products'];

        // First, restore stock for old products
        foreach ($oldProducts as $oldProduct) {
            $p = Product::findOrFail($oldProduct->product_id);
            $p->stock += $oldProduct->quantity;
            $p->save();
        }

        // Delete old products
        CommandProduct::where('commande_id', $commande->id)->delete();

        // Add new products and adjust the stock
        foreach ($products as $product) {
            $p = Product::findOrFail($product['_id']);
            CommandProduct::create([
                'commande_id' => $commande->id,
                'product_id' => $product['_id'],
                'attribute_id'=>$product['attribute_id'],
                'value_id'=>$product['value_id'],
                'quantity' => $product['quantity'],
                'price' => $p->price,
            ]);
            $p->stock -= $product['quantity'];
            $p->save();
        }
        return response()->json(['message' => 'Commande updated successfully!', 'commande' => $commande], 200);
    } catch (\Exception $e) {

        return response()->json(['error' => 'Failed to update Commande', 'message' => $e->getMessage()], 500);
    }
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $commande = Commande::findOrFail($id);
        $commande->delete();

        return response()->json(['message' => 'Order deleted successfully.']);
    }
}
