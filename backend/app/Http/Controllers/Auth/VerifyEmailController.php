<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(
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
}
