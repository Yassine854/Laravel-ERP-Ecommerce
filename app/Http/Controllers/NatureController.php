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

    private function normalizeName($string)
    {
        // Normalize to NFD form
        $normalized = Normalizer::normalize($string, Normalizer::FORM_D);
        // Remove accents and diacritical marks
        $normalized = preg_replace('/[\p{Mn}]/u', '', $normalized);
        // Convert to lowercase
        $normalized = strtolower($normalized);
        // Remove redundant spaces and trim
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        $normalized = trim($normalized);
        // Reduce repeated letters to a single letter
        $normalized = $this->reduceRepeatedLetters($normalized);
        return $normalized;
    }

    // Function to reduce repeated letters to a single letter
    private function reduceRepeatedLetters($string)
    {
        return preg_replace('/(\w)\1+/', '$1', $string);
    }

public function store(Request $request)
{
    $name = $request->input('name');
    $normalizedName = $this->normalizeName($name);
    // Define custom validation rule for uniqueness
    $validator = Validator::make($request->all(), [
        'name' => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) use ($normalizedName) {
                $exists = Nature::where('name',$normalizedName)->exists();
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
        'name' => $request->input('name'),
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
