<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            "password" => "required|string",
            "new_password" => [
                "required",
                "confirmed",
                Rules\Password::defaults(),
            ],
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(["message" => "Invalid password"], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(["message" => "Password changed"]);
    }

    public function changeEmail(Request $request): JsonResponse
    {
        $request->validate([
            "password" => "required|string",
            "email" => "required|email|unique:users",
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(["message" => "Invalid password"], 401);
        }

        $user->email = $request->email;
        $user->save();

        return response()->json(["message" => "Email changed"]);
    }

    public function changeDisplayName(Request $request): JsonResponse
    {
        $request->validate([
            "display_name" => "required|string|max:255",
        ]);

        $user = $request->user();
        $user->display_name = $request->display_name;
        $user->save();

        return response()->json([
            "message" => "Display name changed",
            "display_name" => $user->display_name,
        ]);
    }

    public function changeUsername(Request $request): JsonResponse
    {
        $request->validate([
            "username" =>
                "required|string|alpha_dash|lowercase|max:255|unique:users",
        ]);

        $user = $request->user();
        $user->username = $request->username;
        $user->save();

        return response()->json([
            "message" => "Username changed",
            "username" => $user->username,
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            "password" => "required|string",
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(["message" => "Invalid password"], 401);
        }

        // Delete images
        foreach ($user->media as $media) {
            try {
                unlink($media->getMediaPath());
                $media->delete();
            } catch (\Exception $ignored) {
            }
        }

        $user->delete();

        return response()->json(["message" => "Account deleted"]);
    }
}
