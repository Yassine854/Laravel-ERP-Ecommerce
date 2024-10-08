<?php

namespace App\Http\Controllers;

use App\Models\Parametre;
use Illuminate\Http\Request;
use App\Models\user;
use Illuminate\Support\Facades\Auth;

class ParametreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($admin_id)
    {
        $parametres = Parametre::with('nature')->where('user_id', $admin_id)->get();

        $admin = User::where('_id', $admin_id)->first();

        return response()->json([
            'parametres' => $parametres,
            'admin' => $admin
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $admin_id)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'nature_id' => 'required',
            'address' => 'required|string|max:255',
            'phone' => 'required|numeric|digits:8|unique:users,tel',
            'description' => 'nullable|string|max:255',
            'key_word' => 'nullable|string|max:255',
            'temps_travail' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'url_fb' => 'nullable|string|max:255',
            'url_insta' => 'nullable|string|max:255',
            'url_youtube' => 'nullable|string|max:255',
            'url_tiktok' => 'nullable|string|max:255',
            'url_twiter' => 'nullable|string|max:255',
            'mode_payement' => 'nullable|string|max:255',
        ]);

        $parametre = Parametre::create([
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'phone' => $request->input('phone'),
            'nature_id' => $request->input('nature_id'),
            'description' => $request->input('description'),
            'key_word' => $request->input('key_word'),
            'temps_travail' => $request->input('temps_travail'),
            'email' => $request->input('email'),
            'url_fb' => $request->input('url_fb'),
            'url_insta' => $request->input('url_insta'),
            'url_youtube' => $request->input('url_youtube'),
            'url_tiktok' => $request->input('url_tiktok'),
            'url_twiter' => $request->input('url_twiter'),
            'mode_payement' => $request->input('mode_payement'),
            'user_id' => $admin_id,
        ]);
        $admin = User::where('_id', $admin_id)->first();
        $admin->parametre_id=$parametre->id;
        $admin->save();

        return response()->json($parametre, 201);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nature_id' => 'required',
            'address' => 'required|string|max:255',
            'phone' => 'required|numeric|digits:8|unique:users,tel',
            'description' => 'nullable|string|max:255',
            'key_word' => 'nullable|string|max:255',
            'temps_travail' => 'nullable|string|max:255',
            'email' => 'nullable|string|max:255',
            'url_fb' => 'nullable|string|max:255',
            'url_insta' => 'nullable|string|max:255',
            'url_youtube' => 'nullable|string|max:255',
            'url_tiktok' => 'nullable|string|max:255',
            'url_twiter' => 'nullable|string|max:255',
            'mode_payement' => 'nullable|string|max:255',
        ]);

        $parametre = Parametre::findOrFail($id);
        $parametre->name = $request->input('name');
        $parametre->phone = $request->input('phone');
        $parametre->address = $request->input('address');
        $parametre->nature_id = $request->input('nature_id');
        $parametre->email = $request->input('email');
        $parametre->description = $request->input('description');
        $parametre->key_word = $request->input('key_word');
        $parametre->temps_travail = $request->input('temps_travail');
        $parametre->url_fb = $request->input('url_fb');
        $parametre->url_insta = $request->input('url_insta');
        $parametre->url_youtube = $request->input('url_youtube');
        $parametre->url_tiktok = $request->input('url_tiktok');
        $parametre->url_twiter = $request->input('url_twiter');
        $parametre->mode_payement = $request->input('mode_payement');
        $parametre->save();

        return response()->json($parametre);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $parametre = Parametre::findOrFail($id);

        $parametre->delete();
        return response()->json(null, 204);
    }

    public function show($admin_id)
    {
        // Fetch the parameter with its associated nature
        $parametre = Parametre::with('nature')->where('user_id', $admin_id)->first();

        // Check if the parameter exists
        if (!$parametre) {
            return response()->json(['message' => 'Parametre not found.'], 404);
        }

        return response()->json([
            'parametre' => $parametre,
        ]);
    }

}
