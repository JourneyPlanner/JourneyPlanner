<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use App\Models\JourneyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class JourneyUserController extends Controller
{
    /**
     * Get the users of the journey
     */
    public function index($id): JsonResponse
    {
        // check if the journey exists and if the user is a part of it
        $journey = Journey::find($id);
        if (!$journey | !$journey->users()->where('user_id', auth()->id())->exists()) {
            return abort(404);
        }

        // return the users of the journey in json format
        return response()->json($journey->users()->get());
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
