<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Journey;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserController extends Controller
{
    /**
     * Get all users.
     */
    public function index()
    {
        $validated = request()->validate([
            "per_page" => "nullable|integer|min:1|max:100",
            "search" => "nullable|string",
        ]);

        $perPage = $validated["per_page"] ?? 10;
        $search = $validated["search"] ?? "";

        $users = User::where("username", "like", "%$search%")
            ->orWhere("display_name", "like", "%$search%")
            ->orderBy("username", "asc")
            ->simplePaginate($perPage, ["username", "display_name"])
            ->withQueryString();

        return response()->json($users);
    }

    /**
     * Get a user by username.
     */
    public function show(string $username)
    {
        $user = User::where("username", $username)->firstOrFail([
            "username",
            "display_name",
            "created_at",
        ]);

        return response()->json($user);
    }

    /**
     * Change the password of the authenticated user.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            "password" => "string|nullable",
            "new_password" => [
                "required",
                "confirmed",
                Rules\Password::defaults(),
            ],
        ]);

        $user = $request->user();
        $password = $request->password ?? "";

        if ($user->password && !Hash::check($password, $user->password)) {
            return response()->json(["message" => "Invalid password"], 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(["message" => "Password changed"]);
    }

    /**
     * Change the email of the authenticated user.
     */
    public function changeEmail(Request $request): JsonResponse
    {
        $request->validate([
            "password" => "string|nullable",
            "email" => "required|email|unique:users",
        ]);

        $user = $request->user();
        $password = $request->password ?? "";

        if ($user->password && !Hash::check($password, $user->password)) {
            return response()->json(["message" => "Invalid password"], 401);
        }

        $user->email = $request->email;
        $user->save();

        return response()->json(["message" => "Email changed"]);
    }

    /**
     * Change the display name of the authenticated user.
     */
    public function changeDisplayName(Request $request): JsonResponse
    {
        $request->validate([
            "display_name" => "required|string|max:255",
        ]);

        $user = $request->user();
        $user->display_name = trim($request->display_name);
        $user->save();

        return response()->json([
            "message" => "Display name changed",
            "display_name" => $user->display_name,
        ]);
    }

    /**
     * Change the username of the authenticated user.
     */
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

    /**
     * Delete the account of the authenticated user.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            "password" => "string|nullable",
        ]);

        $user = $request->user();
        $password = $request->password ?? "";

        if ($user->password && !Hash::check($password, $user->password)) {
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

        // Delete all journeys which don't have at least one journey guide and which are not in guest mode
        Journey::whereDoesntHave("users", function (Builder $query) {
            $query->whereIn("role", [1, 2]);
            $query->where("is_guest", false);
        })->delete();

        return response()->json(["message" => "Account deleted"]);
    }
}
