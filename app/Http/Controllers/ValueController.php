<?php

namespace App\Http\Controllers;

use App\Models\Value;
use Illuminate\Http\Request;

class ValueController extends Controller
{
    public function index($attribute_id)
    {
        $values = Value::with('attribute')->where('attribute_id',$attribute_id)->get();
        return response()->json($values);
    }

    public function store(Request $request,$attribute_id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $value = Value::create([
            'name' => $request->input('name'),
            'attribute_id'=>$attribute_id,
        ]);

        return response()->json($value, 201);
    }

    // Update the specified value in storage
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $value = Value::findOrFail($id);
        $value->name = $request->input('name');
        $value->save();

        return response()->json($value);
    }

    public function destroy($id)
    {
        $value = Value::findOrFail($id);
        $value->delete();

        return response()->json(null, 204);
    }

}
