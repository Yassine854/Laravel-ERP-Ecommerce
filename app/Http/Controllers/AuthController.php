<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use MongoDB\Laravel\Eloquent\Casts\ObjectId;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    // register a new user method

public function CreateAdmin(Request $request)
{
    // Validation rules
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email|max:255',
        'tel' => 'required|numeric|digits:8|unique:users,tel',
        'city' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'zip' => 'required|numeric|digits:4',
        'password' => 'required|string|min:8',
    ], [
        // French validation messages
        'name.required' => 'Le nom est requis.',
        'email.required' => "L'adresse e-mail est requise.",
        'email.email' => "L'adresse e-mail doit être valide.",
        'email.unique' => "L'adresse e-mail est déjà utilisée.",
        'tel.required' => 'Le numéro de téléphone est requis.',
        'tel.numeric' => 'Le numéro de téléphone doit être un nombre.',
        'tel.digits' => 'Le numéro de téléphone doit comporter exactement 8 chiffres.',
        'tel.unique' => 'Le numéro de téléphone est déjà utilisé.',
        'city.required' => 'La ville est requise.',
        'address.required' => "L'adresse est requise.",
        'zip.required' => 'Le code postal est requis.',
        'zip.numeric' => 'Le code postal doit être un nombre.',
        'zip.digits' => 'Le code postal doit comporter exactement 4 chiffres.',
        'password.required' => 'Le mot de passe est requis.',
        'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
    ]);

    // If validation fails
    if ($validator->fails()) {
        return response()->json([
            'errors' => $validator->errors(),
        ], 422);
    }

    // Create user
    $user = User::create([
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'tel' => $request->input('tel'),
        'city' => $request->input('city'),
        'address' => $request->input('address'),
        'zip' => $request->input('zip'),
        'blocked' => false,
        'role' => "1",
        'password' => Hash::make($request->input('password')),
    ]);

    $subdomain = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->input('name'))) . $user->id;
    $user->subdomain = $subdomain;
    $user->save();

    $token = $user->createToken('auth_token')->plainTextToken;

        $cookie = cookie('token', $token, 60 * 24, '/', env('SESSION_DOMAIN', '.example.shop'), true, true, false, 'None');

    return response()->json([
        'user' => new UserResource($user),
    ])->withCookie($cookie);
}




    public function register(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
            'blocked' => false,
        ]);
        $subdomain = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['name'])) . $user->id;
        $user->subdomain = $subdomain;
        $user->save();


        $token = $user->createToken('auth_token')->plainTextToken;

        $cookie = cookie('token', $token, 60 * 24, '/', env('SESSION_DOMAIN', '.example.shop'), true, true, false, 'None');

        // $domain = str_replace('://', '://' . $user->subdomain . '.', config('app.url'));
        // return redirect($domain)->withCookie($cookie)->with('user', new UserResource($user));
        return response()->json([
            'user' => new UserResource($user),
        ])->withCookie($cookie);
    }


    // login a user method
    public function login(LoginRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Email or password is incorrect!'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $cookie = cookie('token', $token, 60 * 24, '/', env('SESSION_DOMAIN', '.example.shop'), true, true, false, 'None');
        return response()->json([
            'user' => new UserResource($user),
        ])->withCookie($cookie);
    }

    // logout a user method
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        $cookie = cookie()->forget('token');

        return response()->json([
            'message' => 'Logged out successfully!'
        ])->withCookie($cookie);
    }

    // get the authenticated user method
    public function user(Request $request)
    {
        return new UserResource($request->user());
    }

    public function admins()
    {
        $admins = User::where('role', "1")->get();

        return response()->json([
            'admins' => $admins
        ]);
    }


    public function clients()
    {
        $clients = User::where('role', "2")->get();
        return response()->json([
            'clients' => $clients
        ]);
    }

    public function updateAdmin(Request $request, $id)
{
    // Validate request data
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => [
            'required',
            'email',
            'max:255',
            Rule::unique('users')->ignore($id, '_id'),
        ],
        'tel' => ['nullable', 'integer', 'digits:8'],
        'city' => 'nullable|string|max:255',
        'address' => 'nullable|string|max:255',
        'password' => 'nullable|string|min:6|confirmed',
        'zip' => 'nullable|string|min:4|max:4',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,bmp,gif,svg|max:2048',
    ]);

    // Find the admin user by ID
    $admin = User::findOrFail($id);

    // Update user attributes
    $admin->name = $data['name'];
    $admin->tel = $data['tel'];
    $admin->city = $data['city'];
    $admin->address = $data['address'];
    $admin->zip = $data['zip'];

    // Update email if provided
    if (!empty($data['email'])) {
        $admin->email = $data['email'];
    }

    // Handle image upload
    if ($request->hasFile('image')) {
        // Delete the old image if it exists and a new image is being uploaded
        if ($admin->image && Storage::exists('public/img/profile/' . $admin->image)) {
            Storage::delete('public/img/profile/' . $admin->image);
        }

        // Store the new image
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/img/profile', $imageName);
        $admin->image = $imageName;
    }

    // Update password if provided
    if (!empty($data['password'])) {
        $admin->password = Hash::make($data['password']);
    }

    // Save the updated user record
    $admin->update();

    // Return the updated user as JSON response
    return response()->json($admin);
}



    public function block($id)
    {
        $user = User::findOrFail($id);
        $user->blocked = true;
        $user->save();

        return response()->json(null, 204);
    }

    public function unblock($id)
    {
        $user = User::findOrFail($id);
        $user->blocked = false;
        $user->save();

        return response()->json(null, 204);
    }


    public function updatePassword(Request $request, $id)
    {
        // Find the admin by ID
        $admin = User::findOrFail($id);

        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'currentPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if the current password is correct
        if (!Hash::check($request->currentPassword, $admin->password)) {
            return response()->json([
                'errors' => ['currentPassword' => ['The current password is incorrect.']],
            ], 422);
        }

        // Update the password
        $admin->password = Hash::make($request->newPassword);
        $admin->save();

        return response()->json([
            'user' => $admin,
            'message' => 'Password updated successfully.',
        ]);
    }



    // public function CreateClient(Request $request)
    // {
    //     $data = $request->validated();

    //     $user = User::create([
    //         'name' => $data['name'],
    //         'email' => $data['email'],
    //         'tel' => $data['tel'],
    //         'city' => $data['city'],
    //         'address' => $data['address'],
    //         'zip' => $data['zip'],
    //         'blocked' => false,
    //         'role'=>"1",
    //         'password' => Hash::make($data['password']),
    //     ]);
    //     if ($user->role == "2") {
    //         $subdomain = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $data['name'])) . $user->id;
    //         $user->subdomain = $subdomain;
    //         $user->save();
    //     }

    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     $cookie = cookie('token', $token, 60 * 24); // 1 day

    //     $role = $user->role == "1" ? 'Admin' : 'Client';

    //     return response()->json([
    //         'user' => $user,
    //         'message' => $role . ' ajouté avec succès !'
    //     ])->withCookie($cookie);
    // }

}
