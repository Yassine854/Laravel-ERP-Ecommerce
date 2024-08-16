<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use Illuminate\Http\Request;
use MongoDB\Laravel\Auth\User;

class PackController extends Controller
{
    public function index()
    {
        $packs = Pack::all();
        return response()->json($packs);
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'prix' => 'required|numeric',
        ]);

        $pack = Pack::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'prix' => $request->input('prix'),
        ]);

        return response()->json($pack, 201);
    }

    // Update the specified pack in storage
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'prix' => 'required|numeric',
        ]);

        $pack = Pack::findOrFail($id);
        $pack->title = $request->input('title');
        $pack->description = $request->input('description');
        $pack->prix = $request->input('prix');
        $pack->save();

        return response()->json($pack);
    }

    public function destroy($id)
    {
        $pack = Pack::findOrFail($id);
        $pack->delete();

        return response()->json(null, 204);
    }

    public function updatePack( $admin_id, $pack_id)
{

    $admin = User::findOrFail($admin_id);

    $admin->pack_id = $pack_id;

    $admin->save();

    return response()->json(['message' => 'Pack updated successfully.']);
}




}
