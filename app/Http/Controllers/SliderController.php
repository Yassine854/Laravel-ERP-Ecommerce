<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Slider;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{

    public function index($admin_id)
{
    $sliders = Slider::where('user_id', $admin_id)->get();

    $admin = User::where('_id', $admin_id)->first();

    return response()->json([
        'sliders' => $sliders,
        'admin' => $admin
    ]);
}


    public function create(Request $request,$admin_id)
    {

        $request->validate([
            'title' => 'string|max:255',
            'description' => 'string|max:255',
            'image' => 'required|image'
        ]);

        // return response()->json($request->image);
        // return response()->json($request->hasFile('image'));

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->storeAs('public/img/sliders', $imageName);
        } else {
            // return response()->json(null,500);
            return response()->json(['error' => 'Image is required'], 400);
        }

        $slider = Slider::create([
            'image' => $imageName,
            'title' => $request->input('title'),
            'user_id' => $admin_id,
            'description' => $request->input('description'),
        ]);
        return response()->json($slider, 201);

    }

    public function update(Request $request, $id)
{
    // Validate input fields
    $request->validate([
        'title' => 'string|max:255',
        'description' => 'string|max:255',
        'image' => 'nullable|image' // Make the image field optional
    ]);

    // Find the slider to update
    $slider = Slider::find($id);
    if (!$slider) {
        return response()->json(['error' => 'Slider not found'], 404);
    }

    // Store the new image if provided
    $imageName = $slider->image; // Keep the old image name by default
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imageName = time() . '_' . $image->getClientOriginalName();
        $image->storeAs('public/img/sliders', $imageName);
        // Optionally delete the old image file from storage if necessary
        // Storage::delete('public/img/sliders/' . $slider->image);
    }

    // Update the slider
    $slider->update([
        'image' => $imageName,
        'title' => $request->input('title'),
        'description' => $request->input('description'),
    ]);

    return response()->json($slider, 200);
}




    public function destroy($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->delete();
        return response()->json(null, 204);
    }

}
