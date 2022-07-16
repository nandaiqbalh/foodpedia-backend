<?php

use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// untuk API yang hanya bisa diakse oleh user
Route::middleware('auth:sanctum')->group(function () {
    // hanya bisa diakses ketika sudah login (ada access tokennya)
    Route::get('user', [UserController::class, 'fetch']);
    Route::post('user', [UserController::class, 'updateProfile']);
    Route::post('user/photo', [UserController::class, 'updatePhoto']);
    Route::post('logout', [UserController::class, 'logout']);
});

// ga harus login dulu
Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);

// API FOOD
Route::get('food', [FoodController::class, 'all']);
