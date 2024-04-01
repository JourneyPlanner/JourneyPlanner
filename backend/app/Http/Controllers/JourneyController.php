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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        // Check if the currently logged in user is a member of the requested journey
        if (!$journey->users()->where('user_id', auth()->id())->exists()) {
            return abort(404);
        }

        return response()->json($journey);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Journey $journey)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Journey $journey)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Journey $journey)
    {
        //
    }
}
