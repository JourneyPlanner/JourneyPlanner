<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\CalendarActivity;
use App\Models\Journey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created activity in storage.
     */
    public function store(Request $request, $journey): JsonResponse
    {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyGuide", $journey);

        $validated = $request->validate([
            "name" => "required|string",
            "estimated_duration" => "required|date_format:H:i",
            "mapbox_id" => "nullable|string",
            "opening_hours" => "nullable|string",
            "email" => "nullable|email",
            "phone" => "nullable|string",
            "link" => "nullable|url",
            "cost" => "nullable|numeric",
            "description" => "nullable|string",
            "date" => "nullable|date",
            "time" => "nullable|date_format:H:i",
        ]);

        $activity = new Activity($validated);
        $activity->journey_id = $journey->id;

        if (!$request->mapbox_id) {
            $activity->save();

            if ($validated["date"]) {
                if (!$validated["time"]) {
                    $validated["time"] = "00:00";
                }

                $calendarActivity = new CalendarActivity([
                    "activity_id" => $activity->id,
                    "date" => $validated["date"],
                    "time" => $validated["time"],
                ]);
                $calendarActivity->save();
            }

            return response()->json($activity, 201);
        }

        $geocodingResponse = Http::get(
            "https://api.mapbox.com/search/geocode/v6/forward?q=" .
                $validated["mapbox_id"] .
                "&permanent=true&autocomplete=false&limit=1&access_token=" .
                env("MAPBOX_API_KEY")
        );
        $geocodingData = $geocodingResponse->json()["features"][0];
        $activity->longitude = $geocodingData["geometry"]["coordinates"][0];
        $activity->latitude = $geocodingData["geometry"]["coordinates"][1];
        $activity->address = $geocodingData["properties"]["full_address"];

        if ($validated["date"]) {
            if (!$validated["time"]) {
                $validated["time"] = "00:00";
            }

            $calendarActivity = new CalendarActivity([
                "activity_id" => $activity->id,
                "date" => $validated["date"],
                "time" => $validated["time"],
            ]);
            $calendarActivity->save();
        }

        $activity->save();

        return response()->json($activity, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        //
    }
}
