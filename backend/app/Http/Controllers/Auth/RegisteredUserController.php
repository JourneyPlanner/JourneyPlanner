<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            "display_name" => ["required", "string", "max:255"],
            "email" => [
                "required",
                "string",
                "lowercase",
                "email",
                "max:255",
                "unique:" . User::class,
            ],
            "password" => ["required", "confirmed", Rules\Password::defaults()],
        ]);

        $username = Str::lower(
            preg_replace("/[ ]/", "_", $request->display_name)
        );
        $username = preg_replace("/[^a-z0-9_-]/", "", $username);
        while (User::where("username", $username)->exists()) {
            $username .= Str::lower(Str::random(4));
        }

        $user = User::create([
            "display_name" => $request->display_name,
            "username" => $username,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
