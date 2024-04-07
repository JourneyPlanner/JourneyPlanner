<?php

use App\Http\Controllers\JourneyController;
use App\Http\Controllers\JourneyUserController;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('journey', JourneyController::class)->only('store', 'index', 'show')->middleware('auth:sanctum');

Route::resource('journey/{id}/user', JourneyUserController::class)->only('index')->middleware('auth:sanctum');

Route::post('invite/{id}', [JourneyUserController::class, 'store'])->middleware('auth:sanctum');
