<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(
        Request $request,
        string $id,
        string $hash
    ): JsonResponse {
        $user = User::find($id);

        if (
            !$user ||
            !hash_equals(sha1($user->getEmailForVerification()), (string) $hash)
        ) {
            return response()->json(
                [
                    "message" => "Invalid verification URL.",
                ],
                400
            );
        }

        if (!$user->hasVerifiedEmail()) {
            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
        }

        return response()->json([
            "message" => "Your email address has been verified.",
        ]);
    }

    /**
     * Mark the user's new email address as verified.
     */
    public function verifyPending(Request $request, string $token): JsonResponse
    {
        $user = app(config("verify-new-email.model"))
            ->whereToken($token)
            ->firstOr(["*"], function () {
                return response()->json(
                    [
                        "message" => "Invalid verification URL.",
                    ],
                    400
                );
            })
            ->tap(function ($pendingUserEmail) {
                $pendingUserEmail->activate();
            })->user;

        if (config("verify-new-email.login_after_verification")) {
            Auth::guard()->login(
                $user,
                config("verify-new-email.login_remember")
            );
        }

        return response()->json([
            "message" => "Your email address has been verified.",
        ]);
    }
}
