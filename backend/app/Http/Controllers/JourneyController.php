<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    /**
     * Display all journeys of the authenticated user.
     */
    public function index()
    {
        // Get all journeys of the authenticated user
        $journeys = auth()->user()->journeys()->withPivot('role')->get();

        return response()->json($journeys);
    }

    /**
     * Store the new journey and add the authenticated user to it
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'name' => 'required|string',
                'destination' => 'required|string',
                'from' => 'required|date',
                'to' => 'required|date',
                'invite' => 'required|uuid',
            ]
        );

        $journey = Journey::create($validated);
        // Add the authenticated user to the journey with the role of 1 (journey guide)
        $journey->users()->attach(auth()->id(), ['role' => 1]);

        return response()->json(
            [
                'message' => 'Journey created successfully',
                'journey' => $journey,
            ],
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Journey $journey)
    {
        //
    }

    /**
     * Update the specified journey.
     */
    public function update(Request $request, Journey $journey)
    {
        // Check if the authenticated user is a journey guide
        if ($journey->users()->where('user_id', auth()->id())->withPivot('role')->first()->pivot->role != 1) {
            return abort(404);
        }

        // Validate the request
        $validated = $request->safe()->validate(
            [
                'name' => 'required|string',
                'destination' => 'required|string',
                'from' => 'required|date',
                'to' => 'required|date',
            ]
        )->all();

        $journey->update($validated);
    }

    /**
     * Remove the specified journey from the database.
     */
    public function destroy(Journey $journey)
    {
        // Check if the authenticated user is a journey guide
        if ($journey->users()->where('user_id', auth()->id())->withPivot('role')->first()->pivot->role != 1) {
            return abort(404);
        }

        $journey->delete();
    }
}
