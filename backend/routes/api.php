<?php

use App\Http\Controllers\Journey\Activity\ActivityController;
use App\Http\Controllers\Journey\Activity\CalendarActivityController;
use App\Http\Controllers\Journey\JourneyController;
use App\Http\Controllers\Journey\JourneyUserController;
use App\Http\Controllers\Journey\MediaController;
use App\Http\Controllers\Journey\TemplateController;
use App\Http\Controllers\Journey\UploadController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
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

Route::get("/user/tokens/upload", function (Request $request) {
    return response()->json([
        "token" => $request
            ->user()
            ->createToken("media_upload", ["upload:media"])->plainTextToken,
    ]);
});

Route::apiResource("journey", JourneyController::class);

Route::apiResource("journey/{journey}/activity", ActivityController::class);

Route::apiResource("journey/{journey}/user", JourneyUserController::class)
    ->only("index", "update")
    ->middleware("auth:sanctum");

Route::delete("journey/{journey}/leave", [
    JourneyUserController::class,
    "leave",
]);

Route::get("journey/{journey}/user/me", [
    JourneyUserController::class,
    "currentUserDetails",
])->middleware("auth:sanctum");

Route::apiResource(
    "journey/{journey}/activity/{activity}/calendarActivity",
    CalendarActivityController::class
);

Route::post("invite/{id}", [JourneyUserController::class, "store"])->middleware(
    "auth:sanctum"
);

Route::post("upload", [UploadController::class, "upload"])->middleware([
    "auth:sanctum",
]);

Route::apiResource("journey/{journey}/media", MediaController::class)
    ->middleware("auth:sanctum")
    ->only("index", "show");

Route::get("journey/{journey}/weather", [
    JourneyController::class,
    "getWeather",
]);

Route::post("template", [TemplateController::class, "store"])->middleware(
    "auth:sanctum"
);

Route::get("template", [TemplateController::class, "index"]);
Route::get("template/{journey}", [JourneyController::class, "show"]);
Route::get("template/{journey}/activity", [ActivityController::class, "index"]);
Route::get("user/{username}/template", [
    TemplateController::class,
    "userTemplatesIndex",
]);

Route::get("project", [ProjectController::class, "getProjectData"]);

Route::put("user/change-password", [
    UserController::class,
    "changePassword",
])->middleware("auth:sanctum");

Route::put("user/change-email", [
    UserController::class,
    "changeEmail",
])->middleware("auth:sanctum");

Route::put("user/change-display-name", [
    UserController::class,
    "changeDisplayName",
])->middleware("auth:sanctum");

Route::put("user/change-username", [
    UserController::class,
    "changeUsername",
])->middleware("auth:sanctum");

Route::delete("user/delete-account", [
    UserController::class,
    "deleteAccount",
])->middleware("auth:sanctum");

Route::get("user/{username}", [UserController::class, "show"]);
