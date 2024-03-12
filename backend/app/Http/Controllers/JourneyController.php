<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
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
