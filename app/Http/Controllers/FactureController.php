<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Facture;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($id)
    {
        $factures = Facture::with('admin', 'user', 'products')->where('admin_id', $id)->get();
        return response()->json($factures);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'client_id' => 'required|exists:users,_id',
            'total_amount' => 'required|numeric',
            
        ]);

        // Start a transaction to ensure data integrity

        try {
            // Create the Commande
            $facture = Facture::create([
                'admin_id' => $request->admin_id,
                'user_id' => $validated['client_id'],
                'total_amount' => $validated['total_amount'],
                'status' => 'en attente',
            ]);        

            // Commit the transaction

            return response()->json(['message' => 'Facture created successfully!', 'facture' => $facture], 201);
        } catch (\Exception $e) {
            // Rollback the transaction if something goes wrong

            return response()->json(['error' => 'Failed to create Facture', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Facture $facture)
    {
        //
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
    public function update(Request $request, Facture $facture)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facture $facture)
    {
        //
    }
}
