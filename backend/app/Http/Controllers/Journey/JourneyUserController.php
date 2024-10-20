<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class JourneyUserController extends Controller
{
    /**
     * Get the users of the journey.
     */
    public function index(Journey $journey): JsonResponse
    {
        // authorize the user
        Gate::authorize("view", [$journey, false]);

        // return the users of the journey in json format
        return response()->json(
            $journey->users()->get(["id", "display_name", "username", "role"])
        );
    }

    /**
     * Join a journey with an invite code.
     */
    public function store(Request $request, $invite): JsonResponse
    {
        $journey = Journey::where("invite", $invite)->firstOrFail();

        if ($request->user()->can("view", [$journey, false])) {
            return response()->json(
                [
                    "message" => "You are already a member of this journey",
                    "journey" => $journey->id,
                ],
                200
            );
        }

        // Add the user to the journey
        if ($journey->is_guest) {
            $journey->users()->attach(Auth::id(), ["role" => 1]);

            $journey->is_guest = false;
            $journey->save();
        } else {
            $journey->users()->attach(Auth::id(), ["role" => 0]);
        }

        return response()->json(
            [
                "message" => "You have successfully joined the journey",
                "journey" => $journey->id,
            ],
            201
        );
    }

    /**
     * Get the role of the current user in the journey.
     */
    public function currentUserDetails(Journey $journey): JsonResponse
    {
        $journeyUser = $journey
            ->users()
            ->where("user_id", Auth::id())
            ->firstOrFail(["user_id", "role"]);

        return response()->json($journeyUser);
    }

    /**
     * Update the role of the specified user in the journey.
     */
    public function update(
        Request $request,
        Journey $journey,
        $user
    ): JsonResponse {
        Gate::authorize("update", [$journey, false]);

        if (Auth::id() == $user) {
            return response()->json(
                [
                    "message" => "You cannot update your own role",
                ],
                403
            );
        }

        $validated = $request->validate([
            "role" => "required|integer|numeric|between:0,1",
        ]);

        $journeyUser = $journey
            ->users()
            ->updateExistingPivot($user, ["role" => $validated["role"]]);

        return response()->json(
            [
                "message" => "User role updated successfully",
                "user" => $journeyUser,
            ],
            200
        );
    }

    /**
     * Leave the journey.
     */
    public function leave(Journey $journey)
    {
        if ($journey->is_guest) {
            JourneyController::deleteJourney($journey);

            return response()->json(
                [
                    "message" =>
                    "Journey and journey user removed successfully",
                ],
                200
            );
        }

        // Prevent the user from leaving if they are the only guide
        if (
            $journey
            ->users()
            ->wherePivot("user_id", Auth::id())
            ->wherePivot("role", 1)
            ->exists() &&
            $journey->users()->wherePivot("role", 1)->count() === 1 &&
            $journey->users()->count() !== 1
        ) {
            return response()->json(
                [
                    "message" =>
                    "You cannot leave the journey if you are the only guide",
                ],
                403
            );
        }

        $journey->users()->detach(Auth::id());

        // Remove the journey if the user was the last member
        if ($journey->users()->count() === 0) {
            JourneyController::deleteJourney($journey);

            return response()->json(
                [
                    "message" =>
                    "Journey and journey user removed successfully",
                ],
                200
            );
        }

        return response()->json(
            [
                "message" => "Journey user removed successfully",
            ],
            200
        );
    }
}
