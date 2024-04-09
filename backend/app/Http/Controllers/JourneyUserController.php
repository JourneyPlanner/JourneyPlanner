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
        Gate::authorize('journeyMember', $journey);

        // return the users of the journey in json format
        return response()->json($journey->users()->get(['id', 'firstName', 'lastName', 'role']));
    }

    /**
     * Join a journey with an invite code.
     */
    public function store(Request $request, $invite): JsonResponse
    {
        $journey = Journey::where('invite', $invite)->firstOrFail(['id']);

        if ($request->user()->can('journeyMember', $journey)) {
            return response()->json([
                'message' => 'You are already a member of this journey',
                'journey' => $journey
            ], 200);
        }

        $journey->users()->attach(auth()->id(), ['role' => 0]);

        return response()->json([
            'message' => 'You have successfully joined the journey',
            'journey' => $journey
        ], 201);
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
        $journeyUser = $journey->users()->where('user_id', auth()->id())->firstOrFail(['user_id', 'role']);

        return response()->json($journeyUser);
    }

    /**
     * Update the role of the specified user in the journey.
     */
    public function update(Request $request, $journey, $user): JsonResponse
    {
        Gate::authorize('journeyGuide', Journey::findOrFail($journey));

        if (auth()->user()->id == $user) {
            return response()->json([
                'message' => 'You cannot update your own role',
            ], 403);
        }

        $journeyUser = JourneyUser::where('journey_id', $journey)->where('user_id', $user)->firstOrFail();

        $request->validate([
            'role' => 'required|integer|between:0,1'
        ]);

        $journeyUser->update($request->role);

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => $journeyUser
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JourneyUser $journeyUser)
    {
        //
    }
}
