<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role; // Assuming you have a Role model

class RoleController extends Controller
{
    // Display a listing of roles
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    // Store a newly created role in storage
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $role = Role::create([
            'name' => $request->input('name'),
        ]);

        return response()->json($role, 201);
    }

    // Update the specified role in storage
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->input('name');
        $role->save();

        return response()->json($role);
    }

    // Remove the specified role from storage
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete();

        return response()->json(null, 204);
    }
}
