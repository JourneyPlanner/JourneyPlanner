<?php

use App\Http\Controllers\ActivityController;
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

Route::middleware(["auth:sanctum"])->get("/user", function (Request $request) {
    return $request->user();
});

Route::apiResource("journey", JourneyController::class)->middleware(
    "auth:sanctum"
);

Route::apiResource(
    "journey/{id}/activity",
    ActivityController::class
)->middleware("auth:sanctum");

Route::apiResource("journey/{id}/user", JourneyUserController::class)
    ->only("index", "update")
    ->middleware("auth:sanctum");

Route::delete("journey/{journey}/leave", [
    JourneyUserController::class,
    "leave",
])->middleware("auth:sanctum");

Route::get("journey/{journey}/user/me", [
    JourneyUserController::class,
    "currentUserDetails",
])->middleware("auth:sanctum");

Route::post("invite/{id}", [JourneyUserController::class, "store"])->middleware(
    "auth:sanctum"
);
