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
    public function resend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "email" => "required_without:user_id|nullable|email",
            "user_id" => "required_without:email|nullable|uuid",
        ]);
        $user = $validated["user_id"]
            ? User::find($validated["user_id"])
            : User::where("email", $validated["email"])->first();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        // Always return a successful response to prevent email scraping
        return response()->json(["status" => "verification-link-sent"]);
    }

    /**
     * Send a new email update verification notification.
     */
    public function resendPending(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "email" => "required_without:user_id|nullable|email",
            "user_id" => "required_without:email|nullable|uuid",
        ]);
        $user = $validated["user_id"]
            ? User::find($validated["user_id"])
            : User::where("email", $validated["email"])->first();

        if ($user && $user->getPendingEmail()) {
            $user->resendPendingEmailVerificationMail();
        }

        // Always return a successful response to prevent email scraping
        return response()->json(["status" => "verification-link-sent"]);
    }
}
