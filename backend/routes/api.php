<?php

use App\Http\Controllers\BusinessController;
use App\Http\Controllers\Journey\Activity\ActivityController;
use App\Http\Controllers\Journey\Activity\CalendarActivityController;
use App\Http\Controllers\Journey\JourneyController;
use App\Http\Controllers\Journey\JourneyUserController;
use App\Http\Controllers\Journey\MediaController;
use App\Http\Controllers\Journey\TemplateController;
use App\Http\Controllers\Journey\UploadController;
use App\Http\Controllers\Journey\TemplateRatingController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

Route::middleware(["auth:sanctum"])->get("/me", function (Request $request) {
    $user = $request->user();
    return response()->json([
        "id" => $user->id,
        "email" => $user->email,
        "username" => $user->username,
        "display_name" => $user->display_name,
        "email_needs_verification" => $user->getPendingEmail(),
        "requiresPassword" => !(
            !$user->password || Hash::check("", $user->password)
        ),
        "created_at" => $user->created_at,
        "updated_at" => $user->updated_at,
    ]);
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
    ->only("index", "update", "destroy")
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
)->only("destroy");

Route::post("invite/{id}", [JourneyUserController::class, "store"])->middleware(
    "auth:sanctum"
);

Route::post("upload", [UploadController::class, "upload"])->middleware([
    "auth:sanctum",
]);

Route::get("journey/{journey}/media", [
    MediaController::class,
    "index",
])->middleware(["auth:sanctum"]);

Route::get("journey/{journey}/media/{media}", [MediaController::class, "show"]);

Route::get("journey/{journey}/weather", [
    JourneyController::class,
    "getWeather",
]);

Route::post("template", [TemplateController::class, "store"])->middleware(
    "auth:sanctum"
);

Route::get("template", [TemplateController::class, "index"]);
Route::delete("template/{journey}", [JourneyController::class, "destroy"]);
Route::put("template/{journey}", [TemplateController::class, "update"]);
Route::get("template/{journey}", [TemplateController::class, "show"]);
Route::get("template/{journey}/activity", [ActivityController::class, "index"]);
Route::get("user/{username}/template", [
    TemplateController::class,
    "userTemplatesIndex",
]);
Route::post("template/{journey}/rate", [
    TemplateRatingController::class,
    "rate",
])->middleware("auth:sanctum");
Route::get("template/{journey}/rate", [
    TemplateRatingController::class,
    "show",
])->middleware("auth:sanctum");
Route::get("me/template", [
    TemplateController::class,
    "currentUserTemplatesIndex",
])->middleware("auth:sanctum");

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
Route::get("user", [UserController::class, "index"]);

Route::get("business/{business:slug}", [BusinessController::class, "show"]);
Route::get("business/{business:slug}/image/{image}", [
    BusinessController::class,
    "showImage",
]);
Route::get("business/{business:slug}/templates", [
    BusinessController::class,
    "showTemplates",
]);
Route::get("business/{business:slug}/activities", [
    BusinessController::class,
    "showActivities",
]);
Route::get("me/business", [BusinessController::class, "currentsUserIndex"]);
Route::post("business/{business:slug}/image", [
    BusinessController::class,
    "uploadImage",
])->middleware("auth:sanctum");
Route::delete("business/{business:slug}/image", [
    BusinessController::class,
    "deleteImage",
])->middleware("auth:sanctum");
Route::post("business/{business:slug}/updateTexts", [
    BusinessController::class,
    "updateTexts",
])->middleware("auth:sanctum");
Route::post("business/{business:slug}/updateTemplates", [
    BusinessController::class,
    "updateTemplates",
])->middleware("auth:sanctum");
Route::post("business/{business:slug}/createTemplate", [
    BusinessController::class,
    "createTemplate",
])->middleware("auth:sanctum");
