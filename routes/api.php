<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PackController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ValueController;
use App\Http\Controllers\NatureController;
use App\Http\Controllers\OffresController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ParametreController;
use App\Http\Controllers\ParametresController;

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
Route::post('/CreateAdmin', [AuthController::class, 'CreateAdmin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);


    //Admins
    Route::get('/admins', [AuthController::class, 'admins']);
    Route::post('/admins/create', [AuthController::class, 'NewAdmin']);
    Route::post('/admins/update/{id}', [AuthController::class, 'updateAdmin']);
    Route::put('/admins/updatePassword/{id}', [AuthController::class, 'updatePassword']);

    Route::put('/admins/block/{id}', [AuthController::class, 'block']);
    Route::put('/admins/unblock/{id}', [AuthController::class, 'unblock']);

    //Clients
    Route::get('/clients', [AuthController::class, 'clients']);
    Route::post('/CreateClient', [AuthController::class, 'CreateClient']);
    Route::put('/clients/update/{id}', [AuthController::class, 'updateClient']);
    Route::delete('/clients/delete/{id}', [AuthController::class, 'deleteClient']);

    //Slider
    Route::get('/sliders/{admin_id}', [SliderController::class, 'index']);
    Route::post('/sliders/create/{admin_id}', [SliderController::class, 'create']);
    Route::post('/sliders/update/{slider_id}', [SliderController::class, 'update']);
    Route::delete('/sliders/delete/{slider_id}', [SliderController::class, 'destroy']);

    //Offre
    Route::get('/offres/{pack_id}', [OffreController::class, 'index']);
    Route::post('/offres/create', [OffreController::class, 'create']);
    Route::put('/offres/update/{id}', [OffreController::class, 'update']);
    Route::delete('/offres/destroy/{id}', [OffreController::class, 'destroy']);
    Route::post('/updateOffre/{admin_id}/{offre_id}', [OffreController::class, 'updateOffre']);


    //Pack
    Route::get('/packs', [PackController::class, 'index']);
    Route::post('/packs/create', [PackController::class, 'create']);
    Route::put('/packs/update/{id}', [PackController::class, 'update']);
    Route::delete('/packs/destroy/{id}', [PackController::class, 'destroy']);
    Route::post('/updatePack/{admin_id}/{pack_id}', [PackController::class, 'updatePack']);
    //Parametres

    Route::get('/parametres/{admin_id}', [ParametreController::class, 'index']);
    Route::get('/parametres/show/{admin_id}', [ParametreController::class, 'show']);
    Route::post('/parametres/create/{admin_id}', [ParametreController::class, 'create']);
    Route::put('/parametres/update/{parameter_id}', [ParametreController::class, 'update']);
    Route::delete('/parametres/delete/{parameter_id}', [ParametreController::class, 'destroy']);

    //contacts

    Route::post('/contacts/create', [ContactsController::class, 'create']);
    // Route::get('/contacts/{admin_id}', [ContactsController::class, 'index']);
    // Route::put('/contacts/update/{admin_id}/{contact_id}', [ContactsController::class, 'update']);
    // Route::delete('/contacts/delete/{admin_id}/{contact_id}', [ContactsController::class, 'destroy']);

    // Nature
    Route::get('natures', [NatureController::class, 'index']);
    Route::post('natures', [NatureController::class, 'store']);
    Route::put('natures/{id}', [NatureController::class, 'update']);
    Route::delete('natures/{id}', [NatureController::class, 'destroy']);


    // Category
    Route::get('categories/{nature_id}', [CategoryController::class, 'index']);
    Route::post('categories/{nature_id}', [CategoryController::class, 'store']);
    Route::put('categories/{id}', [CategoryController::class, 'update']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);

    //Attributes
    Route::get('attributes', [AttributeController::class, 'index']);
    Route::post('attributes', [AttributeController::class, 'store']);
    Route::put('attributes/{id}', [AttributeController::class, 'update']);
    Route::delete('attributes/{id}', [AttributeController::class, 'destroy']);

    //Values
    Route::get('values/{attribute_id}', [ValueController::class, 'index']);
    Route::post('values/{attribute_id}', [ValueController::class, 'store']);
    Route::put('values/{id}', [ValueController::class, 'update']);
    Route::delete('values/{id}', [ValueController::class, 'destroy']);

    // Product routes
    Route::get('products/categories/{category_id}', [ProductController::class, 'index']);
    Route::get('products/{admin_id}/{category_id}', [ProductController::class, 'AdminProducts']);
    Route::get('products/{admin_id}', [ProductController::class, 'AllAdminProducts']);
    Route::get('ShowProduct/{product_id}', [ProductController::class, 'show']);
    Route::post('products', [ProductController::class, 'store']);
    Route::post('products/{id}', [ProductController::class, 'update']);
    Route::delete('products/{id}', [ProductController::class, 'destroy']);


    // Commandes
    Route::get('ProductAttributes/{product_id}', [CommandeController::class, 'getAttributesWithValuesForProduct']);

    Route::get('commandes/{user_id}', [CommandeController::class, 'index']);
    Route::post('commandes', [CommandeController::class, 'store']);
    Route::put('commandes/{id}', [CommandeController::class, 'update']);
    Route::delete('commandes/{id}', [CommandeController::class, 'destroy']);
    Route::get('ShowCommande/{id}', [CommandeController::class, 'show']);

    Route::get('NoCommandesInFactures/{admin_id}', [CommandeController::class, 'NoCommandesInFactures']);


    //factures
    Route::get('factures/{admin_id}', [FactureController::class, 'index']);
    Route::get('PrintFacture/{admin_id}', [FactureController::class, 'show']);

    Route::post('factures', [FactureController::class, 'store']);
    Route::put('factures/{id}', [FactureController::class, 'update']);
    Route::delete('factures/{id}', [FactureController::class, 'destroy']);

     // Stock routes
    //  Route::get('stocks', [StockController::class, 'index']);
    //  Route::get('stocks/{id}', [StockController::class, 'show']);
    //  Route::post('stocks', [StockController::class, 'store']);
    //  Route::put('stocks/{id}', [StockController::class, 'update']);
    //  Route::delete('stocks/{id}', [StockController::class, 'destroy']);

    //Dashboard
    Route::get('NbCommandes/{admin_id}', [DashboardController::class, 'NbCommandes']);
    Route::get('NbClients/{admin_id}', [DashboardController::class, 'NbClients']);
    Route::get('NbFactures/{admin_id}', [DashboardController::class, 'NbFactures']);
    Route::get('RecentCommandes/{admin_id}', [DashboardController::class, 'RecentCommandes']);
    Route::get('getUsersByCity/{admin_id}', [DashboardController::class, 'getUsersByCity']);


    // Route::get('/clients', [AuthController::class, 'clients']);
});



// Route::domain('{subdomain}.example.shop')->group(function () {
//     Route::middleware(['auth:sanctum', 'subdomain'])->group(function () {
//         Route::get('/clients', [AuthController::class, 'clients']);
//     });
// });
