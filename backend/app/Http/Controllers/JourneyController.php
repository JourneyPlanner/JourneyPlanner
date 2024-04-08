<?php

namespace App\Http\Controllers;

use App\Http\Requests\JourneyRequest;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class JourneyController extends Controller
{
    /**
     * Display all journeys of the authenticated user.
     */
    public function index(Request $request)
    {
        // Get all journeys of the authenticated user
        $journeys = auth()->user()->journeys()->get(['id', 'name', 'destination', 'from', 'to', 'role']);

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
     * Show the requested journey.
     */
    public function show(Journey $journey)
    {
        // Check if the authenticated user is a member of the requested journey
        Gate::authorize('journeyMember', $journey);

        return response()->json($journey);
    }

    /**
     * Update the specified journey.
     */
    public function update(JourneyRequest $request, Journey $journey)
    {
        Gate::authorize('journeyGuide', $journey);

        // Validate the request
        $validated = $request->safe()->all();

        $journey->update($validated);
    }

    /**
     * Remove the specified journey from the database.
     */
    public function destroy(Request $request, Journey $journey)
    {
        Gate::authorize('journeyGuide', $journey);

        $journey->delete();
    }
}
