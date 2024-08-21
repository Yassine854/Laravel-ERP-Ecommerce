<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    // List all stock items
    public function index()
    {
        $stocks = Stock::with('product')->get();
        return response()->json($stocks);
    }

    // Show a single stock item
    public function show($id)
    {
        $stock = Stock::with('product')->findOrFail($id);
        return response()->json($stock);
    }

    // Create a new stock item
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $stock = new Stock([
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
        ]);

        $stock->save();
        return response()->json($stock, 201);
    }

    // Update an existing stock item
    public function update(Request $request, $id)
    {
        $stock = Stock::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'product_id' => 'sometimes|exists:products,id',
            'quantity' => 'sometimes|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->has('product_id')) {
            $stock->product_id = $request->product_id;
        }
        if ($request->has('quantity')) {
            $stock->quantity = $request->quantity;
        }

        $stock->save();
        return response()->json($stock);
    }

    // Delete a stock item
    public function destroy($id)
    {
        $stock = Stock::findOrFail($id);
        $stock->delete();
        return response()->json(null, 204);
    }
}
