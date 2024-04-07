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
        Gate::authorize('view', $journey);

        // return the users of the journey in json format
        return response()->json($journey->users()->get(['id', 'firstName', 'lastName', 'role']));
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(JourneyUser $journeyUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JourneyUser $journeyUser)
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
