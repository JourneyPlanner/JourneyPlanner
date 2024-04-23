<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use App\Models\JourneyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class JourneyUserController extends Controller
{
    /**
     * Get the users of the journey.
     */
    public function index($id): JsonResponse
    {
        // get the journey by id and authorize the user
        $journey = Journey::findOrFail($id);
        Gate::authorize("journeyMember", $journey);

        // return the users of the journey in json format
        return response()->json(
            $journey->users()->get(["id", "firstName", "lastName", "role"])
        );
    }

    /**
     * Join a journey with an invite code.
     */
    public function store(Request $request, $invite): JsonResponse
    {
        $journey = Journey::where("invite", $invite)->firstOrFail(["id"]);

        if ($request->user()->can("journeyMember", $journey)) {
            return response()->json(
                [
                    "message" => "You are already a member of this journey",
                    "journey" => $journey,
                ],
                200
            );
        }

        $journey->users()->attach(auth()->id(), ["role" => 0]);

        return response()->json(
            [
                "message" => "You have successfully joined the journey",
                "journey" => $journey,
            ],
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(JourneyUser $journeyUser)
    {
        //
    }

    /**
     * Get the role of the current user in the journey.
     */
    public function currentUserDetails(Journey $journey): JsonResponse
    {
        $journeyUser = $journey
            ->users()
            ->where("user_id", auth()->id())
            ->firstOrFail(["user_id", "role"]);

        return response()->json($journeyUser);
    }

    /**
     * Update the role of the specified user in the journey.
     */
    public function update(Request $request, $journey, $user): JsonResponse
    {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyGuide", $journey);

        if (auth()->user()->id == $user) {
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
    public function leave($journey)
    {
        $journey = Journey::findOrFail($journey);

        // Prevent the user from leaving if they are the only guide
        if (
            $journey
                ->users()
                ->wherePivot("user_id", auth()->id())
                ->wherePivot("role", 1)
                ->exists() &&
            $journey->users()->wherePivot("role", 1)->count() !== 1
        ) {
            return response()->json(
                [
                    "message" =>
                        "You cannot leave the journey if you are the only guide",
                ],
                403
            );
        }

        $journey->users()->detach(auth()->id());

        // Remove the journey if the user was the last member
        if ($journey->users()->count() === 0) {
            $journey->delete();

            return response()->json(
                [
                    "message" => "Journey and user removed successfully",
                ],
                200
            );
        }

        return response()->json(
            [
                "message" => "User removed successfully",
            ],
            200
        );
    }
}
