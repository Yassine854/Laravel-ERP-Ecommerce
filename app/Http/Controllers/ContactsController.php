<?php

namespace App\Http\Controllers;

use App\Models\contacts;
use App\Models\User;
use Illuminate\Http\Request;

class ContactsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($admin_id)
    {
        $contacts = contacts::where('user_id', $admin_id)->get();
        $admin = User::where('_id', $admin_id)->first();
        return response()->json([
            'contacts' => $contacts,
            'admin' => $admin
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request, $admin_id)
    {
        $request->validate([
            'name' => 'required',
            'mail' => 'string|max:255',
            'mobile' => 'string|max:255',
            'message' => 'string|max:255',
        ]);
        $contact = contacts::create([
            'name' => $request->input('name'),
            'mail' => $request->input('mail'),
            'mobile' => $request->input('mobile'),
            'message' => $request->input('message'),
        ]);
        return response()->json($contact, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'mail' => 'string|max:255',
            'mobile' => 'string|max:255',
            'message' => 'string|max:255',
        ]);

        $contact = contacts::findOrFail($id);
        $contact->name = $request->input('name');
        $contact->mail = $request->input('mail');
        $contact->mobile = $request->input('mobile');
        $contact->message = $request->input('message');
        $contact->save();
        
        return response()->json($contact);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(contacts $contacts)
    {
        $contacts->delete();
        return response()->json(null, 204);
    }
}
