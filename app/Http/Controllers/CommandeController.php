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
            'products.*.attributes' => 'required|array',
            'products.*.attributes.*.attributes' => 'required|array',
            'products.*.attributes.*.quantity' => [
    'required',
    'integer',
    'min:1',
    function ($attribute, $value, $fail) use ($request) {
        // Ensure $request->products exists and is an array
        if (!isset($request->products) || !is_array($request->products)) {
            return $fail('Invalid product data.');
        }

        preg_match('/products\.(\d+)\.attributes\.(\d+)\.quantity/', $attribute, $matches);

        // Check if matches exist
        if (!isset($matches[1]) || !isset($matches[2])) {
            return $fail('Invalid attribute data.');
        }

        $productIndex = $matches[1];
        $attributeIndex = $matches[2];

        // Ensure the product and attribute indices are valid
        if (!isset($request->products[$productIndex])) {
            return $fail('Product does not exist.');
        }

        $productId = $request->products[$productIndex]['_id'];

        if (!isset($request->products[$productIndex]['attributes'][$attributeIndex])) {
            return $fail('Attribute does not exist.');
        }

        $attributeId = $request->products[$productIndex]['attributes'][$attributeIndex]['attributes'][0]['attribute_id'] ?? null;
        $valueId = $request->products[$productIndex]['attributes'][$attributeIndex]['attributes'][0]['value_id'] ?? null;

        // Check if product exists and retrieve its stock
        $product = Product::find($productId);
        if (!$product) {
            return $fail("Le produit n'existe pas.");
        }

        // Check if the quantity is more than the product stock
        if ($value > $product->stock) {
            return $fail("La quantité ne peut pas dépasser le stock du produit.");
        }

        // Check if attribute value exists and retrieve its stock
        $attributeValue = ProductAttributeValue::where('product_id', $productId)
            ->where('attribute_id', $attributeId)
            ->where('value_id', $valueId)
            ->first();

        if (!$attributeValue) {
            return $fail("L'attribut avec cette valeur n'existe pas.");
        }

        // Check if the quantity is more than the attribute stock
        if ($value > $attributeValue->stock) {
            return $fail("La quantité ne peut pas dépasser le stock des attributs.");
        }
    },
],

        ], [
            'products.*.attributes' => 'Les attributs du produit sont requis.',
            'products.*.attributes.*.attributes' => 'Chaque valeur doit être sélectionnée.',
            'products.*.attributes.*.quantity.required' => 'La quantité est requise.',
            'products.*.attributes.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'products.*.attributes.*.quantity.min' => 'La quantité doit être au moins :min.',
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

                        $attributeValue = ProductAttributeValue::where('product_id', $product['_id'])
                        ->where('attribute_id', $attribute['attribute_id'])
                        ->where('value_id', $attribute['value_id'])
                        ->first();

                        if ($attributeValue) {
                            $attributeValue->stock -= $attributeGroup['quantity'];
                            $attributeValue->save();  // Save the updated stock
                        }

                    }

                    $p->stock-=$attributeGroup['quantity'];
                    $p->save();
                }
                // Update product stock
            }
            return response()->json(['message' => 'Commande created successfully!', 'commande' => $commande], 201);

    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Eager load relationships
        $commande = Commande::with(['admin', 'user', 'products.product', 'products.product.attributes.attribute', 'products.product.attributes.value'])
                            ->findOrFail($id);

        // Transform the response to include products and their attributes in the required structure
        $commande->products = $commande->products->map(function ($commandProduct) {
            // Gather the product attributes in the desired nested structure
            $attributesArray = $commandProduct->product->attributes->map(function ($attribute) {
                return [
                    'attribute_id' => $attribute->attribute_id,
                    'value_id' => $attribute->value_id,
                ];
            })->values()->toArray();

            // Calculate the quantity per attribute
            $quantityPerAttribute = count($attributesArray) > 0 ? $commandProduct->quantity / count($attributesArray) : 0;

            return [
                '_id' => $commandProduct->product_id,
                'quantity' => $quantityPerAttribute, // Divide quantity by the number of attributes
                'attributes' => $attributesArray, // Directly include attributes
            ];
        });

        // Prepare the final response
        $response = [
            'admin_id' => $commande->admin_id,
            'client_id' => $commande->user_id,
            'products' => $commande->products,
            'total_amount' => $commande->total_amount,
        ];

        return response()->json($response);
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
            'products.*.attributes' => 'required|array',
            'products.*.attributes.*.attributes' => 'required|array',
            'products.*.attributes.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],
        ], [
            'products.*.attributes' => 'Les attributs du produit sont requis.',
            'products.*.attributes.*.attributes' => 'Chaque valeur doit être sélectionnée.',
            'products.*.attributes.*.quantity.required' => 'La quantité est requise.',
            'products.*.attributes.*.quantity.integer' => 'La quantité doit être un nombre entier.',
            'products.*.attributes.*.quantity.min' => 'La quantité doit être au moins :min.',
        ]);

        // Get the existing Commande
        $commande = Commande::findOrFail($id);

        // Get existing products for the Commande
        $existingProducts = CommandProduct::where('commande_id', $commande->id)
            ->get()
            ->keyBy(function ($item) {
                return $item->product_id . '-' . $item->attribute_id . '-' . $item->value_id;
            });

        // Process the updated products
        $products = $validated['products'];

        // Validation flag
        $validationPassed = true;
        $errorMessages = [];

        foreach ($products as $product) {
            $p = Product::findOrFail($product['_id']);
            foreach ($product['attributes'] as $attributeGroup) {
                foreach ($attributeGroup['attributes'] as $attribute) {
                    $key = $product['_id'] . '-' . $attribute['attribute_id'] . '-' . $attribute['value_id'];
                    $existingQuantity = isset($existingProducts[$key]) ? $existingProducts[$key]->quantity : 0;
                    $newQuantity = $attributeGroup['quantity'];
                    $quantityDifference = $newQuantity - $existingQuantity;

                    // Stock validation: Check if quantity exceeds stock
                    if ($quantityDifference > 0) {
                        if ($p->stock < $quantityDifference) {
                            $errorMessages[] = "La quantité du produit {$p->name} dépasse le stock disponible.";
                            $validationPassed = false;
                        }

                        $attributeValue = ProductAttributeValue::where('product_id', $product['_id'])
                            ->where('attribute_id', $attribute['attribute_id'])
                            ->where('value_id', $attribute['value_id'])
                            ->first();

                        if ($attributeValue && $attributeValue->stock < $quantityDifference) {
                            $errorMessages[] = "La quantité de l'attribut {$attributeValue->name} dépasse le stock disponible.";
                            $validationPassed = false;
                        }
                    }
                }
            }
        }

        // If validation fails, return error
        if (!$validationPassed) {
            return response()->json(['errors' => $errorMessages], 422);
        }

        // Update the Commande details once all validations pass
        $commande->update([
            'admin_id' => $request->admin_id,
            'user_id' => $validated['client_id'],
            'total_amount' => $validated['total_amount'],
        ]);

        // Process product and attribute updates
        foreach ($products as $product) {
            $p = Product::findOrFail($product['_id']);
            foreach ($product['attributes'] as $attributeGroup) {
                foreach ($attributeGroup['attributes'] as $attribute) {
                    $key = $product['_id'] . '-' . $attribute['attribute_id'] . '-' . $attribute['value_id'];
                    $existingQuantity = isset($existingProducts[$key]) ? $existingProducts[$key]->quantity : 0;
                    $newQuantity = $attributeGroup['quantity'];
                    $quantityDifference = $newQuantity - $existingQuantity;

                    if (isset($existingProducts[$key])) {
                        $existingProduct = $existingProducts[$key];
                        $existingProduct->update([
                            'quantity' => $newQuantity,
                            'price' => $p->price,
                        ]);
                        unset($existingProducts[$key]);
                    } else {
                        CommandProduct::create([
                            'commande_id' => $commande->id,
                            'product_id' => $product['_id'],
                            'price' => $p->price,
                            'quantity' => $newQuantity,
                            'attribute_id' => $attribute['attribute_id'],
                            'value_id' => $attribute['value_id'],
                        ]);
                    }

                    // Adjust stock
                    if ($quantityDifference < 0) {
                        $p->stock += abs($quantityDifference);
                    } else {
                        $p->stock -= $quantityDifference;
                    }

                    $attributeValue = ProductAttributeValue::where('product_id', $product['_id'])
                        ->where('attribute_id', $attribute['attribute_id'])
                        ->where('value_id', $attribute['value_id'])
                        ->first();

                    if ($attributeValue) {
                        if ($quantityDifference < 0) {
                            $attributeValue->stock += abs($quantityDifference);
                        } else {
                            $attributeValue->stock -= $quantityDifference;
                        }
                        $attributeValue->save();
                    }
                }
            }
            $p->save();
        }

        // Delete remaining entries
        if ($existingProducts->isNotEmpty()) {
            CommandProduct::whereIn('id', $existingProducts->pluck('id'))->delete();
        }

        return response()->json(['message' => 'Commande updated successfully!', 'commande' => $commande], 200);
    }








    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $commande = Commande::findOrFail($id);
        CommandProduct::where('commande_id', $commande->id)->delete();

        $commande->delete();

        return response()->json(['message' => 'Order deleted successfully.']);
    }
}
