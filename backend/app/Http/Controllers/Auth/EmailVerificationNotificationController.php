<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "email" => "required|email",
        ]);
        $user = User::where("email", $validated["email"])->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        // Always return a successful response to prevent email scraping
        return response()->json(["status" => "verification-link-sent"]);
    }
}
