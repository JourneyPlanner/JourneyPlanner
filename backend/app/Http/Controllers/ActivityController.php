<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\CalendarActivity;
use App\Models\Journey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use DateTime;
use DateInterval;

class ActivityController extends Controller
{
    /**
     * Get all activities of the specified journey.
     */
    public function index($journey)
    {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyMember", $journey);

        return response()->json(
            $journey->activities()->with("calendarActivities")->get()
        );
    }

    /**
     * Store a newly created activity in storage.
     */
    public function store(Request $request, $journey): JsonResponse
    {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyGuide", $journey);

        // Validate the request
        $validated = $request->validate([
            "name" => "required|string",
            "estimated_duration" => "required|date_format:H:i",
            "mapbox_id" => "nullable|string",
            "mapbox_full_address" => "nullable|string",
            "address" => "nullable|string",
            "opening_hours" => "nullable|string",
            "email" => "nullable|email",
            "phone" => "nullable|string",
            "link" => "nullable|url",
            "cost" => "nullable|string",
            "description" => "nullable|string",
            "date" => "nullable|date",
            "time" => "nullable|date_format:H:i",
        ]);

        if (
            array_key_exists("mapbox_full_address", $validated) &&
            $validated["mapbox_full_address"]
        ) {
            $validated["address"] = "";
        } elseif (
            array_key_exists("address", $validated) &&
            $validated["address"]
        ) {
            $validated["mapbox_full_address"] = $validated["address"];
        }

        // Create the activity
        $activity = new Activity($validated);
        $activity->journey_id = $journey->id;

        // Get the longitude and latitude of the address if it exists
        if (
            array_key_exists("mapbox_full_address", $validated) &&
            $validated["mapbox_full_address"]
        ) {
            $geocodingData = [];
            $geocodingResponse = Http::get(
                "https://api.mapbox.com/search/geocode/v6/forward?q=" .
                    $validated["mapbox_full_address"] .
                    "&permanent=true&autocomplete=false&limit=1&access_token=" .
                    env("MAPBOX_API_KEY")
            );
            $geocodingData = $geocodingResponse->json();

            if (
                array_key_exists("features", $geocodingData) &&
                count($geocodingData["features"]) !== 0
            ) {
                $geocodingData = $geocodingResponse->json()["features"][0];
                $activity->longitude =
                    $geocodingData["geometry"]["coordinates"][0];
                $activity->latitude =
                    $geocodingData["geometry"]["coordinates"][1];
                $activity->mapbox_full_address =
                    $geocodingData["properties"]["full_address"];
                if ($validated["address"] = "") {
                    $activity->address =
                        $geocodingData["properties"]["full_address"];
                }
            }
        }

        $activity->save();

        // Create the calendar activity if the date is provided
        if (array_key_exists("date", $validated) && $validated["date"]) {
            if (!array_key_exists("time", $validated) || !$validated["time"]) {
                $validated["time"] = "00:00";
            }

            $start = new DateTime(
                $validated["date"] . " " . $validated["time"]
            );
            $end = clone $start;
            $end->add(
                new DateInterval(
                    "PT" .
                        substr($activity->estimated_duration, 0, 2) .
                        "H" .
                        substr($activity->estimated_duration, 3) .
                        "M"
                )
            );

            $calendarActivity = new CalendarActivity([
                "activity_id" => $activity->id,
                "start" => $start,
                "end" => $end,
            ]);
            $calendarActivity->save();
            return response()->json($activity->load("calendarActivities"), 201);
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

        return response()->json($activity->load("calendarActivities"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($journey, Request $request, Activity $activity)
    {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyGuide", $journey);

        // Validate the request
        $validated = $request->validate([
            "name" => "required|string",
            "estimated_duration" => "required|date_format:H:i",
            "mapbox_id" => "nullable|string",
            "mapbox_full_address" => "nullable|string",
            "address" => "nullable|string",
            "opening_hours" => "nullable|string",
            "email" => "nullable|email",
            "phone" => "nullable|string",
            "link" => "nullable|url",
            "cost" => "nullable|string",
            "description" => "nullable|string",
            "date" => "nullable|date",
            "time" => "nullable|date_format:H:i",
        ]);

        if (
            array_key_exists("mapbox_full_address", $validated) &&
            $validated["mapbox_full_address"]
        ) {
            $validated["address"] = "";
        } elseif (
            array_key_exists("address", $validated) &&
            $validated["address"]
        ) {
            $validated["mapbox_full_address"] = $validated["address"];
        }

        $oldMapboxFullAddress = $activity->mapbox_full_address;

        // Update the activity
        $activity->update($validated);

        // Update the longitude and latitude of the address if it exists and if they have changed
        if (
            array_key_exists("mapbox_full_address", $validated) &&
            $validated["mapbox_full_address"] &&
            $oldMapboxFullAddress !== $validated["mapbox_full_address"]
        ) {
            $geocodingData = [];
            $geocodingResponse = Http::get(
                "https://api.mapbox.com/search/geocode/v6/forward?q=" .
                    $validated["mapbox_full_address"] .
                    "&permanent=true&autocomplete=false&limit=1&access_token=" .
                    env("MAPBOX_API_KEY")
            );
            $geocodingData = $geocodingResponse->json();

            if (
                array_key_exists("features", $geocodingData) &&
                count($geocodingData["features"]) !== 0
            ) {
                $geocodingData = $geocodingResponse->json()["features"][0];
                $activity->longitude =
                    $geocodingData["geometry"]["coordinates"][0];
                $activity->latitude =
                    $geocodingData["geometry"]["coordinates"][1];
                $activity->mapbox_full_address =
                    $geocodingData["properties"]["full_address"];
                if ($validated["address"] = "") {
                    $activity->address =
                        $geocodingData["properties"]["full_address"];
                }
            }
        }

        $activity->save();

        // Create the calendar activity if the date is provided
        if (array_key_exists("date", $validated) && $validated["date"]) {
            if (!array_key_exists("time", $validated) || !$validated["time"]) {
                $validated["time"] = "00:00";
            }

            $start = new DateTime(
                $validated["date"] . " " . $validated["time"]
            );
            $end = clone $start;
            $end->add(
                new DateInterval(
                    "PT" .
                        substr($activity->estimated_duration, 0, 2) .
                        "H" .
                        substr($activity->estimated_duration, 3) .
                        "M"
                )
            );

            $calendarActivity = new CalendarActivity([
                "activity_id" => $activity->id,
                "start" => $start,
                "end" => $end,
            ]);
            $calendarActivity->save();
            return response()->json($activity->load("calendarActivities"), 201);
        }

        return response()->json($activity, 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($journey, Activity $activity)
    {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyGuide", $journey);

        $activity->delete();
    }
}
