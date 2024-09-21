<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Normalizer;

class CategoryController extends Controller
{
    // List all categories
    public function index($nature_id)
{
    $categories = Category::with('nature') // Filter by nature_id
                          ->where('nature_id', $nature_id) // Eager load the 'nature' relationship
                          ->get(); // Retrieve the results
    return response()->json($categories); // Return the results as JSON
}

    // Show a single category
    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    private function normalizeName($string)
{
    // Normaliser en forme NFD (décompose les caractères accentués)
    $normalized = Normalizer::normalize($string, Normalizer::FORM_D);

    // Supprimer les accents et les marques diacritiques
    $normalized = preg_replace('/[\p{Mn}]/u', '', $normalized);

    // Convertir la chaîne en minuscules pour l'insensibilité à la casse
    $normalized = strtolower($normalized);

    // Supprimer les espaces redondants et supprimer les espaces superflus
    $normalized = preg_replace('/\s+/', ' ', $normalized);
    $normalized = trim($normalized);

    // Réduire les lettres répétées à une seule lettre
    $normalized = $this->reduceRepeatedLetters($normalized);

    // S'assurer que la chaîne ne commence ni ne se termine par un espace
    $normalized = preg_replace('/^\s+|\s+$/', '', $normalized);

    // Supprimer les caractères spéciaux sauf les tirets et les espaces
    $normalized = preg_replace('/[^a-z0-9\- ]/', '', $normalized);

    // Limiter la longueur pour éviter les noms excessivement longs
    if (strlen($normalized) > 255) {
        $normalized = substr($normalized, 0, 255);
    }

    // Empêcher les tirets en début ou en fin
    $normalized = preg_replace('/^-+|-+$/', '', $normalized);

    // Empêcher les tirets multiples consécutifs
    $normalized = preg_replace('/-{2,}/', '-', $normalized);

    return $normalized;
}


// Function to reduce repeated letters to a single letter
private function reduceRepeatedLetters($string)
{
    return preg_replace('/(\w)\1+/', '$1', $string);
}



    // Create a new category
    public function store(Request $request,$nature_id)
    {
        $name = $request->input('name');

        // Normalize the name before any operation
        $normalizedName = $this->normalizeName($name);

        // Remove the trailing 's' for uniqueness check
        $normalizedNameWithoutS = rtrim($normalizedName, 's');
        $normalizedNameWithS = $normalizedName . 's';

        $validator = Validator::make($request->all(), [

            'description' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,_id',
            'name' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-zA-ZÀ-ÿ\s\-]+$/u', // Allow letters, accented letters, spaces, and hyphens

            function ($attribute, $value, $fail) use ($normalizedNameWithoutS,$normalizedName,$normalizedNameWithS) {
                // Check if a record with the normalized name or its variant (without 's') already exists
                $exists = Category::where(function ($query) use ($normalizedName, $normalizedNameWithoutS,$normalizedNameWithS) {
                    $query->where('name', $normalizedName)
                          ->orWhere('name', $normalizedNameWithoutS)
                          ->orWhere('name', $normalizedNameWithS);
                })->exists();

                if ($exists) {
                    $fail('Le nom existe déjà.');
                }
            },
        ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category = new Category([
            'name' => strtolower($normalizedName),
            'description' => $request->description,
            'user_id' => $request->user_id,
            'nature_id'=>$nature_id
        ]);

        $category->save();
        return response()->json($category, 201);
    }

    // Update an existing category
    public function update(Request $request, $id)
    {
        $name = $request->input('name');

        // Normalize the name before any operation
        $normalizedName = $this->normalizeName($name);

        // Remove the trailing 's' for uniqueness check
        $normalizedNameWithoutS = rtrim($normalizedName, 's');
        $normalizedNameWithS = $normalizedName . 's';

        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:255',
            'user_id' => 'sometimes|exists:users,_id',
            'name' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-zA-ZÀ-ÿ\s\-]+$/u', // Allow letters, accented letters, spaces, and hyphens

            function ($attribute, $value, $fail) use ($normalizedNameWithoutS,$normalizedName,$normalizedNameWithS) {
                // Check if a record with the normalized name or its variant (without 's') already exists
                $exists = Category::where(function ($query) use ($normalizedName, $normalizedNameWithoutS,$normalizedNameWithS) {
                    $query->where('name', $normalizedName)
                          ->orWhere('name', $normalizedNameWithoutS)
                          ->orWhere('name', $normalizedNameWithS);
                })->exists();

                if ($exists) {
                    $fail('Le nom existe déjà.');
                }
            },
        ],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has('name')) {
            $category->name = strtolower($normalizedName);
        }
        if ($request->has('user_id')) {
            $category->user_id = $request->user_id;
        }
        if ($request->has('description')) {
            $category->description = $request->description;
        }

        $category->save();
        return response()->json($category);
    }

    // Delete a category
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(null, 204);
    }
}
