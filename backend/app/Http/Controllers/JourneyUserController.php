<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use App\Models\JourneyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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
        if ($journey->users()->where('user_id', auth()->id())->doesntExist()) {
            abort(403, 'Unauthorized');
        }

        // return the users of the journey in json format
        return response()->json($journey->users()->get(['id', 'firstName', 'lastName', 'role']));
    }

    /**
     * Join a journey with an invite code.
     */
    public function store(Request $request, $invite): JsonResponse
    {
        $journey = Journey::where('invite', $invite)->firstOrFail(['id']);

        if ($journey->users()->where('user_id', auth()->id())->exists()) {
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, JourneyUser $journeyUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JourneyUser $journeyUser)
    {
        //
    }
}
