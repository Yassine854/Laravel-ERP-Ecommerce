<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->get();
        return response()->json($attributes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $attribute = Attribute::create([
            'name' => $request->input('name'),
        ]);

        return response()->json($attribute, 201);
    }

    // Update the specified attribute in storage
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $attribute = Attribute::findOrFail($id);
        $attribute->name = $request->input('name');
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
