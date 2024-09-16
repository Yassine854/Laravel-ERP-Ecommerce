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


    // Create a new category
    public function store(Request $request,$nature_id)
    {
        $name = $request->input('name');
        $normalizedName = $this->normalizeName($name);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,_id',
            function ($attribute, $value, $fail) use ($normalizedName) {
                $exists = Category::where('name',$normalizedName)->exists();
                if ($exists) {
                    $fail('Le nom existe déjà.');
                }
            },
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $category = new Category([
            'name' => $request->name,
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
        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:255',
            'user_id' => 'sometimes|exists:users,_id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has('name')) {
            $category->name = $request->name;
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
