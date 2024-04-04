<?php

namespace App\Http\Controllers;

use App\Http\Requests\JourneyRequest;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    /**
     * Display all journeys of the authenticated user.
     */
    public function index(Request $request)
    {
        if ($request->user()->cannot('viewAny', Journey::class)) {
            abort(403);
        }

        // Get all journeys of the authenticated user
        $journeys = auth()->user()->journeys()->withPivot('role')->get();

        return response()->json($journeys);
    }

    /**
     * Store the new journey and add the authenticated user to it
     */
    public function store(Request $request): JsonResponse
    {
        if ($request->user()->cannot('create', Journey::class)) {
            abort(403);
        }

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
    public function show(Request $request, Journey $journey)
    {
        // Check if the authenticated user is a member of the requested journey
        if ($request->user()->cannot('view', $journey)) {
            abort(403);
        }

        return response()->json($journey);
    }

    /**
     * Update the specified journey.
     */
    public function update(JourneyRequest $request, Journey $journey)
    {
        // Check if the authenticated user can update the journey
        if ($request->user()->cannot('update', $journey)) {
            abort(403);
        }

        // Validate the request
        $validated = $request->safe()->all();

        $journey->update($validated);
    }

    /**
     * Remove the specified journey from the database.
     */
    public function destroy(Request $request, Journey $journey)
    {
        // Check if the authenticated user can delete the journey
        if ($request->user()->cannot('delete', $journey)) {
            abort(403);
        }

        $journey->delete();
    }
}
