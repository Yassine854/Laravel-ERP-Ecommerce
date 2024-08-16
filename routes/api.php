<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\OffresController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\ParametresController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SliderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


/*Role of users:
0:super admin
1:admin entreprise
2:client
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/CreateUser', [AuthController::class, 'CreateUser']);
    Route::post('/logout', [AuthController::class, 'logout']);



   /////////////////////Super Admin //////////////////////////////////////////////
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/roles', [RoleController::class, 'index']);
        Route::post('/roles/create', [RoleController::class, 'create']);
        Route::put('/roles/update/{id}', [RoleController::class, 'update']);
        Route::delete('/roles/delete/{id}', [RoleController::class, 'destroy']);
    });

    //Admins
    Route::get('/admins', [AuthController::class, 'admins']);
    Route::put('/admins/update/{id}', [AuthController::class, 'updateAdmin']);
    Route::put('/admins/updatePassword/{id}', [AuthController::class, 'updatePassword']);

    Route::put('/admins/block/{id}', [AuthController::class, 'block']);
    Route::put('/admins/unblock/{id}', [AuthController::class, 'unblock']);

    //Clients
    Route::get('/user', [AuthController::class, 'user']);

    //Slider
    Route::get('/sliders/{admin_id}', [SliderController::class, 'index']);
    Route::post('/sliders/create/{admin_id}', [SliderController::class, 'create']);
    Route::post('/sliders/update/{slider_id}', [SliderController::class, 'update']);
    Route::delete('/sliders/delete/{slider_id}', [SliderController::class, 'destroy']);

    //Offre
    Route::get('/offres', [OffreController::class, 'index']);
    Route::post('/offres/create', [OffreController::class, 'create']);
    Route::put('/offres/update/{id}', [OffreController::class, 'update']);
    Route::delete('/offres/destroy/{id}', [OffreController::class, 'destroy']);


    //Pack
    Route::get('/packs', [PackController::class, 'index']);
    Route::post('/packs/create', [PackController::class, 'create']);
    Route::put('/packs/update/{id}', [PackController::class, 'update']);
    Route::delete('/packs/destroy/{id}', [PackController::class, 'destroy']);

    //Parametres

    Route::get('/parametres/{admin_id}', [ParametreController::class, 'index']);
    Route::get('/parametres/show/{admin_id}', [ParametreController::class, 'show']);
    Route::post('/parametres/create/{admin_id}', [ParametreController::class, 'create']);
    Route::put('/parametres/update/{parameter_id}', [ParametreController::class, 'update']);
    Route::delete('/parametres/delete/{parameter_id}', [ParametreController::class, 'destroy']);

    //contacts

    Route::get('/contacts/{admin_id}', [ContactsController::class, 'index']);
    Route::post('/contacts/create/{admin_id}', [ContactsController::class, 'create']);
    Route::put('/contacts/update/{admin_id}/{contact_id}', [ContactsController::class, 'update']);
    Route::delete('/contacts/delete/{admin_id}/{contact_id}', [ContactsController::class, 'destroy']);


      /////////////////////Admin //////////////////////////////////////////////
      Route::post('/updatePack/{admin_id}/{pack_id}', [PackController::class, 'updatePack']);
      Route::post('/updateOffre/{admin_id}/{offre_id}', [OffreController::class, 'updateOffre']);





    // Route::get('/clients', [AuthController::class, 'clients']);
});



Route::domain('{subdomain}.example.shop')->group(function () {
    Route::middleware(['auth:sanctum', 'subdomain'])->group(function () {
        Route::get('/clients', [AuthController::class, 'clients']);
    });
});
