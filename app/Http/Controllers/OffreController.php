<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use Illuminate\Http\Request;
use MongoDB\Laravel\Auth\User;

class OffreController extends Controller
{

    public function index($pack_id)
    {
        $offres = Offre::with('pack')->where('pack_id',$pack_id)->get();
        return response()->json($offres);
    }

    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'prix' => 'required|numeric',
        ]);

        $offre = Offre::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'prix' => $request->input('prix'),
            'pack_id' => $request->input('pack_id'),
        ]);

        return response()->json($offre, 201);
    }

    // Update the specified offre in storage
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'prix' => 'required|numeric',
        ]);

        $offre = Offre::findOrFail($id);
        $offre->title = $request->input('title');
        $offre->description = $request->input('description');
        $offre->prix = $request->input('prix');
        $offre->save();

        return response()->json($offre);
    }

    public function destroy($id)
    {
        $offre = Offre::findOrFail($id);
        $offre->delete();

        return response()->json(null, 204);
    }

    public function updateOffre( $admin_id, $offre_id)
    {

        $admin = User::findOrFail($admin_id);

        $admin->offre_id = $offre_id;

        $admin->save();

        return response()->json(['message' => 'Offre updated successfully.']);
    }

}
