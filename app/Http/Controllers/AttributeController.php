<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\Request;
use Normalizer;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->get();
        return response()->json($attributes);
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

        // Réduire les lettres répétées à une seule ou deux lettres
        $normalized = $this->reduceRepeatedLetters($normalized);

        // S'assurer que la chaîne ne commence ni ne se termine par un espace
        $normalized = preg_replace('/^\s+|\s+$/', '', $normalized);

        // Supprimer les caractères spéciaux sauf les tirets, les espaces et les points
        $normalized = preg_replace('/[^a-z0-9\-\. ]/', '', $normalized);

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

    private function reduceRepeatedLetters($string)
    {
        // Allow up to 2 consecutive letters or digits
        return preg_replace('/([a-z0-9])\1{2,}/', '$1$1', $string);
    }

    public function store(Request $request)
    {

        $name = $request->input('name');

        // Normalize the name before any operation
        $normalizedName = $this->normalizeName($name);

        // Remove the trailing 's' for uniqueness check
        $normalizedNameWithoutS = rtrim($normalizedName, 's');
        $normalizedNameWithS = $normalizedName . 's';

        $request->validate([
            'name' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-zA-Z0-9À-ÿ\s\-\.]+$/u', // Updated regex to allow dots

            function ($attribute, $value, $fail) use ($normalizedNameWithoutS,$normalizedName,$normalizedNameWithS) {
                // Check if a record with the normalized name or its variant (without 's') already exists
                $exists = Attribute::where(function ($query) use ($normalizedName, $normalizedNameWithoutS,$normalizedNameWithS) {
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

        $attribute = Attribute::create([
            'name' => strtolower($normalizedName),
        ]);

        return response()->json($attribute, 201);
    }

    // Update the specified attribute in storage
    public function update(Request $request, $id)
    {
        $name = $request->input('name');

        // Normalize the name before any operation
        $normalizedName = $this->normalizeName($name);

        // Remove the trailing 's' for uniqueness check
        $normalizedNameWithoutS = rtrim($normalizedName, 's');
        $normalizedNameWithS = $normalizedName . 's';
        $request->validate([
            'name' => [
            'required',
            'string',
            'max:255',
            'regex:/^[a-zA-Z0-9À-ÿ\s\-\.]+$/u', // Updated regex to allow dots

            function ($attribute, $value, $fail) use ($normalizedNameWithoutS,$normalizedName,$normalizedNameWithS) {
                // Check if a record with the normalized name or its variant (without 's') already exists
                $exists = Attribute::where(function ($query) use ($normalizedName, $normalizedNameWithoutS,$normalizedNameWithS) {
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

        $attribute = Attribute::findOrFail($id);
        $attribute->name =strtolower($normalizedName);

        $attribute->save();

        return response()->json($attribute);
    }

    public function destroy($id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete();

        return response()->json(null, 204);
    }

}
