<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response|JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        if ($user && ! $user->hasVerifiedEmail()) {
            return response()->json(
                [
                    'message' => 'Your email address is not verified.',
                ],
                401
            );
        }

        $request->authenticate();

        $request->session()->regenerate();

        return response()->noContent();
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }

    /**
     * Generate Microsoft login URL.
     */
    public function redirectToProviderMicrosoft()
    {
        return response()->json([
            'url' => Socialite::driver('microsoft')->redirect()->getTargetUrl(),
        ]);
    }

    /**
     * Handle the callback from the Google provider.
     */
    public function handleProviderCallbackGoogle(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        /**
         * @disregard P1013 Undefined method
         * This is needed for my PHP plugin to not complain about the method
         */
        $googleUser = Socialite::driver('google-one-tap')
            ->stateless()
            ->userFromToken($validated['token']);

        // Create a new user if the user does not exist
        $user = $this->findOrCreateUser($googleUser, 'google');

        Auth::login($user);

        return response()->json($user);
    }

    /**
     * Handle the callback from the Microsoft provider.
     */
    public function handleProviderCallbackMicrosoft()
    {
        /**
         * @disregard P1013 Undefined method
         * This is needed for my PHP plugin to recognize the method
         */
        $microsoftUser = Socialite::driver('microsoft')->stateless()->user();

        // Create a new user if the user does not exist
        $user = $this->findOrCreateUser($microsoftUser, 'microsoft');

        Auth::login($user);

        return response()->json($user);
    }

    /**
     * Find or create a user from the OAuth user.
     */
    private function findOrCreateUser($oauthUser, $type): User
    {
        if ($type === 'google') {
            $user = User::firstOrNew(['google_id' => $oauthUser->getId()]);
        } elseif ($type === 'microsoft') {
            $user = User::firstOrNew(['microsoft_id' => $oauthUser->getId()]);
        }

        if (! $user->exists) {
            $user = User::firstOrNew(['email' => $oauthUser->getEmail()]);

            if (! $user->exists) {
                $user->display_name = $oauthUser->getName();
                $user->username = User::generateUsername(
                    $oauthUser->getNickname() ?? $oauthUser->getName()
                );
                $user->email = $oauthUser->getEmail();
            }

            if ($type === 'google') {
                $user->google_id = $oauthUser->getId();
            } elseif ($type === 'microsoft') {
                $user->microsoft_id = $oauthUser->getId();
            }
            $user->save();
        }

        return $user;
    }
}
