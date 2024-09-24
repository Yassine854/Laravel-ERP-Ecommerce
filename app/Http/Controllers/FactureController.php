<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FactureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($admin_id)
    {
        $factures = Facture::where('admin_id', $admin_id)->with('admin', 'user', 'command','command.user','command.products')->get();
        return response()->json($factures);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation des données
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:users,_id',
            'commande_id' => 'required|exists:commandes,_id',
            'facture_date' => 'required|date',
            'facture_tva' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',

        ]);

        // Gérer les erreurs de validation
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Créer la facture après validation
        $facture = Facture::create($request->all());
        return response()->json($facture, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Retrieve the facture by its ID
        $facture = Facture::with([
            'command',
            'command.products'
        ])->findOrFail($id);

        // Format the data to include all necessary details
        $data = [
            'serial_number' => $facture->serial_number,
            'admin' => $facture->admin->name,
            'facture_date' => $facture->facture_date,
            'facture_tva' => $facture->facture_tva,
            'total_amount' => $facture->total_amount,
            'status' => $facture->status,
            'commande' => $facture->command,
        ];

        // Return the data (you can pass this to a view or return it as JSON)
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Facture $facture)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $facture = Facture::find($id);

        if (!$facture) {
            return response()->json(['message' => 'Facture introuvable'], 404);
        }

        // Validation des données pour la mise à jour
        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:users,_id',
            'commande_id' => 'required|exists:commandes,_id',
            'facture_date' => 'required|date',
            'facture_tva' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Mise à jour des données
        $facture->update($request->all());

        return response()->json($facture);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $facture = Facture::find($id);

        if (!$facture) {
            return response()->json(['message' => 'Facture introuvable'], 404);
        }

        $facture->delete();

        return response()->json(['message' => 'Facture supprimée avec succès']);
    }
}
