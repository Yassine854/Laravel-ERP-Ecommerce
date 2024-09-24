<?php

namespace App\Http\Controllers;


use App\Models\Facture;
use App\Models\Product;
use App\Models\Commande;
use Illuminate\Http\Request;
use App\Models\CommandProduct;
use App\Models\CommandeProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductAttributeValue;
use App\Models\CommandProductAttribute;

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $commandes = Commande::with('admin', 'user','products.attributes.attribute')->where('admin_id', $id)->get();
        return response()->json($commandes);
    }

    public function NoCommandesInFactures($admin_id)
    {
        $factureCommandeIds = Facture::pluck('commande_id');

        // Fetch commandes that are not in the factureCommandeIds array
        $commandes = Commande::with('admin', 'user', 'products')
            ->where('admin_id', $admin_id)
            ->whereNotIn('_id', $factureCommandeIds) // Filter commandes not in factures
            ->get();

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

    // public function store(Request $request)
    // {
    //     // Validate the request
    //     $validated = $request->validate([
    //         'client_id' => 'required|exists:users,_id',
    //         'total_amount' => 'required|numeric',
    //         'products' => 'required|array',
    //         'products.*._id' => 'required|exists:products,_id',
    //         'products.*.attributes' => 'required|array',
    //         'products.*.attributes.*.attributes' => 'required|array',
    //         'products.*.attributes.*.quantity' => [
    //             'required',
    //             'integer',
    //             'min:1',
    //             function ($attribute, $value, $fail) use ($request) {
    //                 // Ensure $request->products exists and is an array
    //                 if (!isset($request->products) || !is_array($request->products)) {
    //                     return $fail('Invalid product data.');
    //                 }

    //                 preg_match('/products\.(\d+)\.attributes\.(\d+)\.quantity/', $attribute, $matches);

    //                 // Check if matches exist
    //                 if (!isset($matches[1]) || !isset($matches[2])) {
    //                     return $fail('Invalid attribute data.');
    //                 }

    //                 $productIndex = $matches[1];
    //                 $attributeIndex = $matches[2];

    //                 // Ensure the product and attribute indices are valid
    //                 if (!isset($request->products[$productIndex])) {
    //                     return $fail('Product does not exist.');
    //                 }

    //                 $productId = $request->products[$productIndex]['_id'];

    //                 if (!isset($request->products[$productIndex]['attributes'][$attributeIndex])) {
    //                     return $fail('Attribute does not exist.');
    //                 }

    //                 $attributeId = $request->products[$productIndex]['attributes'][$attributeIndex]['attributes'][0]['attribute_id'] ?? null;
    //                 $valueId = $request->products[$productIndex]['attributes'][$attributeIndex]['attributes'][0]['value_id'] ?? null;

    //                 // Check if product exists and retrieve its stock
    //                 $product = Product::find($productId);
    //                 if (!$product) {
    //                     return $fail("Le produit n'existe pas.");
    //                 }

    //                 // Check if the quantity is more than the product stock
    //                 if ($value > $product->stock) {
    //                     return $fail("La quantité ne peut pas dépasser le stock du produit.");
    //                 }

    //                 // Check if attribute value exists and retrieve its stock
    //                 $attributeValue = ProductAttributeValue::where('product_id', $productId)
    //                     ->where('attribute_id', $attributeId)
    //                     ->where('value_id', $valueId)
    //                     ->first();

    //                 if (!$attributeValue) {
    //                     return $fail("L'attribut avec cette valeur n'existe pas.");
    //                 }

    //                 // Check if the quantity is more than the attribute stock
    //                 if ($value > $attributeValue->stock) {
    //                     return $fail("La quantité ne peut pas dépasser le stock des attributs.");
    //                 }
    //             },
    //         ],

    //     ], [
    //         'products.*.attributes' => 'Les attributs du produit sont requis.',
    //         'products.*.attributes.*.attributes' => 'Chaque valeur doit être sélectionnée.',
    //         'products.*.attributes.*.quantity.required' => 'La quantité est requise.',
    //         'products.*.attributes.*.quantity.integer' => 'La quantité doit être un nombre entier.',
    //         'products.*.attributes.*.quantity.min' => 'La quantité doit être au moins :min.',
    //     ]);


    //     // Start a transaction to ensure data integrity

    //     // Create the Commande
    //     $commande = Commande::create([
    //         'admin_id' => $request->admin_id,
    //         'user_id' => $validated['client_id'],
    //         'total_amount' => $validated['total_amount'],
    //         'status' => 'en attente',
    //     ]);

    //     $products = $validated['products'];
    //     foreach ($products as $product) {
    //         $p = Product::findOrFail($product['_id']);
    //         foreach ($product['attributes'] as $attributeGroup) {
    //             foreach ($attributeGroup['attributes'] as $attribute) {
    //                 // Create CommandProduct entries for each product with its attributes
    //                 CommandProduct::create([
    //                     'commande_id' => $commande->id,
    //                     'product_id' => $product['_id'],
    //                     'price' => $p->price,
    //                     'quantity' => $attributeGroup['quantity'],
    //                     'attribute_id' => $attribute['attribute_id'],
    //                     'value_id' => $attribute['value_id'],
    //                 ]);

    //                 $attributeValue = ProductAttributeValue::where('product_id', $product['_id'])
    //                     ->where('attribute_id', $attribute['attribute_id'])
    //                     ->where('value_id', $attribute['value_id'])
    //                     ->first();

    //                 if ($attributeValue) {
    //                     $attributeValue->stock -= $attributeGroup['quantity'];
    //                     $attributeValue->save();  // Save the updated stock
    //                 }
    //             }

    //             $p->stock -= $attributeGroup['quantity'];
    //             $p->save();
    //         }
    //         // Update product stock
    //     }
    //     return response()->json(['message' => 'Commande created successfully!', 'commande' => $commande], 201);
    // }
    
    
    public function store(Request $request)
    {
        // Valider les données de la requête
        $validated = $request->validate([
            'admin_id' => 'required|exists:users,_id',
            'user_id' => 'required|exists:users,_id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,_id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.attributes' => 'required|array',
            'products.*.attributes.*.attribute_id' => 'required|exists:attributes,_id',
            'products.*.attributes.*.value_id' => 'required|exists:values,_id',
        ]);
    
        $totalAmount = 0;
    
        try {
            // Étape 1 : Création de la commande
            $commande = Commande::create([
                'admin_id' => $validated['admin_id'],
                'user_id' => $validated['user_id'],
                'total_amount' => 0, // Sera calculé plus tard
                'status' => 'pending', // Statut par défaut
            ]);
    
            // Étape 2 : Boucler sur chaque produit
            foreach ($validated['products'] as $productData) {
                $productId = $productData['product_id'];
    
                // Récupérer le produit pour vérifier le stock
                $product = Product::findOrFail($productId);
    
                // Vérifier si le stock global du produit est suffisant
                if ($product->stock < $productData['quantity']) {
                    throw new \Exception('Le stock est insuffisant pour le produit ' . $product->name);
                }
    
                // Déduire la quantité du stock global du produit
                $product->stock -= $productData['quantity'];
                $product->save();
    
                // Calculer le prix total pour le produit
                $productPrice = $product->price * $productData['quantity'];
                $totalAmount += $productPrice;
    
                // Créer le CommandProduct
                $commandProduct = CommandProduct::create([
                    'commande_id' => $commande->id,
                    'product_id' => $productId,
                    'quantity' => $productData['quantity'],
                    'price' => $productPrice, // Enregistrer le prix total
                ]);
    
                // Déduire les quantités de stock des attributs et les associer au produit de la commande
                foreach ($productData['attributes'] as $attributeData) {
                    // Récupérer la valeur de l'attribut
                    $productAttributeValue = ProductAttributeValue::where('product_id', $productId)
                        ->where('attribute_id', $attributeData['attribute_id'])
                        ->where('value_id', $attributeData['value_id'])
                        ->firstOrFail();
    
                    // Vérifier si le stock de l'attribut est suffisant
                    if ($productAttributeValue->stock < $productData['quantity']) {
                        throw new \Exception('Le stock est insuffisant pour l\'attribut du produit ' . $product->name);
                    }
    
                    // Déduire la quantité du stock de l'attribut
                    $productAttributeValue->stock -= $productData['quantity'];
                    $productAttributeValue->save();
    
                    // Créer et attacher les attributs uniques à CommandProduct
                    $commandProduct->attributes()->create([
                        'attribute_id' => $attributeData['attribute_id'],
                        'value_id' => $attributeData['value_id'],
                    ]);
                }
            }
    
            // Étape 3 : Mettre à jour le montant total de la commande
            $commande->update(['total_amount' => $totalAmount]);
    
            return response()->json([
                'message' => 'Commande créée avec succès.',
                'commande' => $commande,
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la commande.',
                'error' => $e->getMessage(),
            ], 500);
        }
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

    
    
    public function update(Request $request, $commandeId)
{
    // Valider les données de la requête
    $validated = $request->validate([
        'admin_id' => 'required|exists:users,_id',
        'user_id' => 'required|exists:users,_id',
        'products' => 'required|array',
        'products.*.product_id' => 'required|exists:products,_id',
        'products.*.quantity' => 'required|integer|min:1',
        'products.*.attributes' => 'required|array',
        'products.*.attributes.*.attribute_id' => 'required|exists:attributes,_id',
        'products.*.attributes.*.value_id' => 'required|exists:values,_id',
    ]);

    try {

        // Récupérer la commande existante
        $commande = Commande::findOrFail($commandeId);

        // Supprimer tous les produits associés à la commande
        foreach ($commande->products as $commandProduct) {
            // Restaurer le stock avant de supprimer
            $product = Product::findOrFail($commandProduct->product_id);
            $product->stock += $commandProduct->quantity;
            $product->save();

            // Restaurer les stocks des attributs
            foreach ($commandProduct->attributes as $commandProductAttribute) {
                $productAttributeValue = ProductAttributeValue::where('product_id', $commandProduct->product_id)
                    ->where('attribute_id', $commandProductAttribute->attribute_id)
                    ->where('value_id', $commandProductAttribute->value_id)
                    ->firstOrFail();
                    
                $productAttributeValue->stock += $commandProduct->quantity;
                $productAttributeValue->save();
            }

            // Supprimer les attributs associés au produit de la commande
            $commandProduct->attributes()->delete();
        }

        // Supprimer tous les produits de la commande
        $commande->products()->delete();

        $totalAmount = 0;
        $uniqueProducts = [];

        // Étape 1 : Boucler sur les produits pour accumuler les données
        foreach ($validated['products'] as $productData) {
            $productId = $productData['product_id'];

            // Initialiser les données du produit s'il n'est pas déjà dans le tableau
            if (!isset($uniqueProducts[$productId])) {
                $uniqueProducts[$productId] = [
                    'quantity' => 0,
                    'attributes' => [],
                ];
            }

            // Incrémenter la quantité
            $uniqueProducts[$productId]['quantity'] += $productData['quantity'];

            // Collecter les attributs uniques pour ce produit
            foreach ($productData['attributes'] as $attributeData) {
                $uniqueProducts[$productId]['attributes'][] = [
                    'attribute_id' => $attributeData['attribute_id'],
                    'value_id' => $attributeData['value_id'],
                ];
            }
        }

        // Étape 2 : Vérification du stock et mise à jour des produits et attributs
        foreach ($uniqueProducts as $productId => $data) {
            // Récupérer le produit et calculer le stock disponible
            $product = Product::findOrFail($productId);

            // Vérifier si le stock global du produit est suffisant
            if ($product->stock < $data['quantity']) {
                throw new \Exception('Le stock est insuffisant pour le produit ' . $product->name);
            }

            // Boucler sur les attributs du produit pour vérifier et déduire le stock
            foreach ($data['attributes'] as $attributeData) {
                // Récupérer la valeur de l'attribut
                $productAttributeValue = ProductAttributeValue::where('product_id', $productId)
                    ->where('attribute_id', $attributeData['attribute_id'])
                    ->where('value_id', $attributeData['value_id'])
                    ->firstOrFail();

                // Vérifier si le stock de l'attribut est suffisant
                if ($productAttributeValue->stock < $data['quantity']) {
                    throw new \Exception('Le stock est insuffisant pour l\'attribut du produit ' . $product->name);
                }
            }
        }

        // Étape 3 : Mise à jour des produits de la commande et du stock
        foreach ($uniqueProducts as $productId => $data) {
            // Récupérer le produit à nouveau pour déduire le stock et calculer le prix
            $product = Product::findOrFail($productId);

            // Déduire la quantité du stock global du produit
            $product->stock -= $data['quantity'];
            $product->save();

            // Calculer le prix total pour le produit
            $productPrice = $product->price * $data['quantity'];
            $totalAmount += $productPrice;

            // Créer le nouveau CommandProduct
            $commandProduct = CommandProduct::create([
                'commande_id' => $commande->id,
                'product_id' => $productId,
                'quantity' => $data['quantity'],
                'price' => $productPrice, // Enregistrer le prix total
            ]);

            // Déduire les quantités de stock des attributs et les associer au produit de la commande
            foreach ($data['attributes'] as $attributeData) {
                // Récupérer la valeur de l'attribut
                $productAttributeValue = ProductAttributeValue::where('product_id', $productId)
                    ->where('attribute_id', $attributeData['attribute_id'])
                    ->where('value_id', $attributeData['value_id'])
                    ->firstOrFail();

                // Déduire la quantité du stock de l'attribut
                $productAttributeValue->stock -= $data['quantity'];
                $productAttributeValue->save();

                // Créer et attacher les attributs uniques à CommandProduct
                $commandProduct->attributes()->create([
                    'attribute_id' => $attributeData['attribute_id'],
                    'value_id' => $attributeData['value_id'],
                ]);
            }
        }

        // Étape 4 : Mettre à jour le montant total de la commande
        $commande->update(['total_amount' => $totalAmount]);


        return response()->json([
            'message' => 'Commande mise à jour avec succès.',
            'commande' => $commande,
        ], 200);

    } catch (\Exception $e) {
        // Rollback en cas d'erreur

        return response()->json([
            'message' => 'Erreur lors de la mise à jour de la commande.',
            'error' => $e->getMessage(),
        ], 500);
    }
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
    // public function update(Request $request, $id)
    // {
    //     // Validate the request
    //     $validated = $request->validate([
    //         'client_id' => 'required|exists:users,_id',
    //         'total_amount' => 'required|numeric',
    //         'products' => 'required|array',
    //         'products.*._id' => 'required|exists:products,_id',
    //         'products.*.attributes' => 'required|array',
    //         'products.*.attributes.*.attributes' => 'required|array',
    //         'products.*.attributes.*.quantity' => [
    //             'required',
    //             'integer',
    //             'min:1',
    //         ],
    //     ], [
    //         'products.*.attributes' => 'Les attributs du produit sont requis.',
    //         'products.*.attributes.*.attributes' => 'Chaque valeur doit être sélectionnée.',
    //         'products.*.attributes.*.quantity.required' => 'La quantité est requise.',
    //         'products.*.attributes.*.quantity.integer' => 'La quantité doit être un nombre entier.',
    //         'products.*.attributes.*.quantity.min' => 'La quantité doit être au moins :min.',
    //     ]);

    //     // Get the existing Commande
    //     $commande = Commande::findOrFail($id);

    //     // Get existing products for the Commande
    //     $existingProducts = CommandProduct::where('commande_id', $commande->id)
    //         ->get()
    //         ->keyBy(function ($item) {
    //             return $item->product_id . '-' . $item->attribute_id . '-' . $item->value_id;
    //         });

    //     // Process the updated products
    //     $products = $validated['products'];

    //     // Validation flag
    //     $validationPassed = true;
    //     $errorMessages = [];

    //     foreach ($products as $product) {
    //         $p = Product::findOrFail($product['_id']);
    //         foreach ($product['attributes'] as $attributeGroup) {
    //             foreach ($attributeGroup['attributes'] as $attribute) {
    //                 $key = $product['_id'] . '-' . $attribute['attribute_id'] . '-' . $attribute['value_id'];
    //                 $existingQuantity = isset($existingProducts[$key]) ? $existingProducts[$key]->quantity : 0;
    //                 $newQuantity = $attributeGroup['quantity'];
    //                 $quantityDifference = $newQuantity - $existingQuantity;

    //                 // Stock validation: Check if quantity exceeds stock
    //                 if ($quantityDifference > 0) {
    //                     if ($p->stock < $quantityDifference) {
    //                         $errorMessages[] = "La quantité du produit {$p->name} dépasse le stock disponible.";
    //                         $validationPassed = false;
    //                     }

    //                     $attributeValue = ProductAttributeValue::where('product_id', $product['_id'])
    //                         ->where('attribute_id', $attribute['attribute_id'])
    //                         ->where('value_id', $attribute['value_id'])
    //                         ->first();

    //                     if ($attributeValue && $attributeValue->stock < $quantityDifference) {
    //                         $errorMessages[] = "La quantité de l'attribut {$attributeValue->name} dépasse le stock disponible.";
    //                         $validationPassed = false;
    //                     }
    //                 }
    //             }
    //         }
    //     }

    //     // If validation fails, return error
    //     if (!$validationPassed) {
    //         return response()->json(['errors' => $errorMessages], 422);
    //     }

    //     // Update the Commande details once all validations pass
    //     $commande->update([
    //         'admin_id' => $request->admin_id,
    //         'user_id' => $validated['client_id'],
    //         'total_amount' => $validated['total_amount'],
    //     ]);

    //     // Process product and attribute updates
    //     foreach ($products as $product) {
    //         $p = Product::findOrFail($product['_id']);
    //         foreach ($product['attributes'] as $attributeGroup) {
    //             foreach ($attributeGroup['attributes'] as $attribute) {
    //                 $key = $product['_id'] . '-' . $attribute['attribute_id'] . '-' . $attribute['value_id'];
    //                 $existingQuantity = isset($existingProducts[$key]) ? $existingProducts[$key]->quantity : 0;
    //                 $newQuantity = $attributeGroup['quantity'];
    //                 $quantityDifference = $newQuantity - $existingQuantity;

    //                 if (isset($existingProducts[$key])) {
    //                     $existingProduct = $existingProducts[$key];
    //                     $existingProduct->update([
    //                         'quantity' => $newQuantity,
    //                         'price' => $p->price,
    //                     ]);
    //                     unset($existingProducts[$key]);
    //                 } else {
    //                     CommandProduct::create([
    //                         'commande_id' => $commande->id,
    //                         'product_id' => $product['_id'],
    //                         'price' => $p->price,
    //                         'quantity' => $newQuantity,
    //                         'attribute_id' => $attribute['attribute_id'],
    //                         'value_id' => $attribute['value_id'],
    //                     ]);
    //                 }

    //                 // Adjust stock
    //                 if ($quantityDifference < 0) {
    //                     $p->stock += abs($quantityDifference);
    //                 } else {
    //                     $p->stock -= $quantityDifference;
    //                 }

    //                 $attributeValue = ProductAttributeValue::where('product_id', $product['_id'])
    //                     ->where('attribute_id', $attribute['attribute_id'])
    //                     ->where('value_id', $attribute['value_id'])
    //                     ->first();

    //                 if ($attributeValue) {
    //                     if ($quantityDifference < 0) {
    //                         $attributeValue->stock += abs($quantityDifference);
    //                     } else {
    //                         $attributeValue->stock -= $quantityDifference;
    //                     }
    //                     $attributeValue->save();
    //                 }
    //             }
    //         }
    //         $p->save();
    //     }

    //     // Delete remaining entries
    //     if ($existingProducts->isNotEmpty()) {
    //         CommandProduct::whereIn('id', $existingProducts->pluck('id'))->delete();
    //     }

    //     return response()->json(['message' => 'Commande updated successfully!', 'commande' => $commande], 200);
    // }








    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
{
    $commande = Commande::findOrFail($id);

    // First, find and delete all the command products related to this commande
    $commandProducts = CommandProduct::where('commande_id', $commande->id)->get();

    foreach ($commandProducts as $commandProduct) {
        // Delete related attributes in CommandProductAttribute
        CommandProductAttribute::where('command_product_id', $commandProduct->id)->delete();

        // Delete the command product itself
        $commandProduct->delete();
    }

    // Now delete the commande
    $commande->delete();

    return response()->json(['message' => 'Order and related products deleted successfully.']);
}

}
