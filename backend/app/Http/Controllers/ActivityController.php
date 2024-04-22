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
     * Get all activities of the specified journey.
     */
    public function index($journey)
    {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyMember", $journey);

        return response()->json($journey->activities()->with('calendarActivities')->get());
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
            "full_address" => "nullable|string",
            "mapbox_id" => "nullable|string",
            "opening_hours" => "nullable|string",
            "email" => "nullable|email",
            "phone" => "nullable|string",
            "link" => "nullable|url",
            "cost" => "nullable|string",
            "description" => "nullable|string",
            "date" => "nullable|date",
            "time" => "nullable|date_format:H:i",
        ]);

        $activity = new Activity($validated);
        $activity->journey_id = $journey->id;

        if (!array_key_exists("full_address", $validated) || !$validated["full_address"]) {
            $activity->save();

            if (array_key_exists("date", $validated) && $validated["date"]) {
                if (!array_key_exists("time", $validated) || !$validated["time"]) {
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
            $validated["full_address"] .
            "&permanent=true&autocomplete=false&limit=1&access_token=" .
            env("MAPBOX_API_KEY")
        );
        $geocodingData = $geocodingResponse->json()["features"][0];
        $activity->longitude = $geocodingData["geometry"]["coordinates"][0];
        $activity->latitude = $geocodingData["geometry"]["coordinates"][1];
        $activity->address = $geocodingData["properties"]["full_address"];

        $activity->save();

        if (array_key_exists("date", $validated) && $validated["date"]) {
            if (!array_key_exists("time", $validated) || !$validated["time"]) {
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

    /**
     * Display the specified activity.
     */
    public function show($journey, Activity $activity)
    {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyMember", $journey);

        return response()->json($activity->load('calendarActivities'));
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
