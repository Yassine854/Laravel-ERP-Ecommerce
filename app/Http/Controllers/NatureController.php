<?php

namespace App\Http\Controllers;

use App\Models\Nature;
use App\Rules\UniqueNature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Normalizer;
class NatureController extends Controller
{
    // List all natures
    public function index()
    {
        $natures = Nature::all();
        return response()->json($natures);
    }

    // Show a single nature
    public function show($id)
    {
        $nature = Nature::findOrFail($id);
        return response()->json($nature);
    }

    // Create a new nature
// Normalize and store names consistently in your database
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




// Store method
public function store(Request $request)
{
    $name = $request->input('name');

    // Normalize the name before any operation
    $normalizedName = $this->normalizeName($name);

    // Remove the trailing 's' for uniqueness check
    $normalizedNameWithoutS = rtrim($normalizedName, 's');
    $normalizedNameWithS = $normalizedName . 's';

    // Define custom validation rule for uniqueness
    $validator = Validator::make($request->all(), [
        'name' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-zA-ZÀ-ÿ\s\-]+$/u', // Allow letters, accented letters, spaces, and hyphens

            function ($attribute, $value, $fail) use ($normalizedNameWithoutS,$normalizedName,$normalizedNameWithS) {
                // Check if a record with the normalized name or its variant (without 's') already exists
                $exists = Nature::where(function ($query) use ($normalizedName, $normalizedNameWithoutS,$normalizedNameWithS) {
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

    // If validation fails, return errors
    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // Save the normalized name to ensure uniqueness
    $nature = new Nature([
        'name' => strtolower($normalizedName), // Save the original name
    ]);

    $nature->save();

    return response()->json($nature, 201);
}




    // Update an existing nature
    public function update(Request $request, $id)
    {
        $nature = Nature::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has('name')) {
            $nature->name = $request->name;
        }

        $nature->save();
        return response()->json($nature);
    }

    // Delete a nature
    public function destroy($id)
    {
        $nature = Nature::findOrFail($id);
        $nature->delete();
        return response()->json(null, 204);
    }
}
