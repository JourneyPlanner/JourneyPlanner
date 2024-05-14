<?php

namespace App\Http\Controllers;

use App\Http\Requests\JourneyRequest;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

class JourneyController extends Controller
{
    /**
     * Display all journeys of the authenticated user.
     */
    public function index(Request $request)
    {
        // Get all journeys of the authenticated user
        $journeys = auth()
            ->user()
            ->journeys()
            ->get(["id", "name", "destination", "from", "to", "role"]);

        return response()->json($journeys);
    }

    /**
     * Store the new journey and add the authenticated user to it
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "name" => "required|string",
            "destination" => "required|string",
            "from" => "required|date",
            "to" => "required|date",
            "invite" => "required|uuid",
            "mapbox_id" => "nullable|string",
            "mapbox_full_address" => "nullable|string",
        ]);

        if (
            array_key_exists("mapbox_full_address", $validated) &&
            $validated["mapbox_full_address"]
        ) {
            $validated["destination"] = "";
        } elseif (
            array_key_exists("destination", $validated) &&
            $validated["destination"]
        ) {
            $validated["mapbox_full_address"] = $validated["destination"];
        }

        $journey = new Journey($validated);

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
                    config("map.mapbox_api_key")
            );
            $geocodingData = $geocodingResponse->json();

            if (
                array_key_exists("features", $geocodingData) &&
                count($geocodingData["features"]) !== 0
            ) {
                $geocodingData = $geocodingResponse->json()["features"][0];
                $journey->longitude =
                    $geocodingData["geometry"]["coordinates"][0];
                $journey->latitude =
                    $geocodingData["geometry"]["coordinates"][1];
                $journey->mapbox_full_address =
                    $geocodingData["properties"]["full_address"];
                if ($validated["destination"] === "") {
                    $journey->destination =
                        $geocodingData["properties"]["full_address"];
                }
            }
        }

        $journey->save();
        // Add the authenticated user to the journey with the role of 1 (journey guide)
        $journey->users()->attach(auth()->id(), ["role" => 1]);

        return response()->json(
            [
                "message" => "Journey created successfully",
                "journey" => $journey,
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
        Gate::authorize("journeyMember", $journey);

        return response()->json($journey);
    }

    /**
     * Update the specified journey.
     */
    public function update(Request $request, Journey $journey)
    {
        Gate::authorize("journeyGuide", $journey);

        // Validate the request
        $validated = $request->validate([
            "name" => "required|string",
            "destination" => "required|string",
            "from" => "required|date",
            "to" => "required|date",
            "mapbox_id" => "nullable|string",
            "mapbox_full_address" => "nullable|string",
        ]);

        $previousMapboxFullAddress = $journey->mapbox_full_address;

        if (
            array_key_exists("mapbox_full_address", $validated) &&
            $validated["mapbox_full_address"] !== $previousMapboxFullAddress
        ) {
            $validated["destination"] = "";
        } elseif (
            array_key_exists("destination", $validated) &&
            $validated["destination"]
        ) {
            $validated["mapbox_full_address"] = $validated["destination"];
        }

        $journey->update($validated);

        // Get the longitude and latitude of the address if it exists
        if (
            array_key_exists("mapbox_full_address", $validated) &&
            $validated["mapbox_full_address"] !== $previousMapboxFullAddress
        ) {
            $geocodingData = [];
            $geocodingResponse = Http::get(
                "https://api.mapbox.com/search/geocode/v6/forward?q=" .
                    $validated["mapbox_full_address"] .
                    "&permanent=true&autocomplete=false&limit=1&access_token=" .
                    config("map.mapbox_api_key")
            );
            $geocodingData = $geocodingResponse->json();

            if (
                array_key_exists("features", $geocodingData) &&
                count($geocodingData["features"]) !== 0
            ) {
                $geocodingData = $geocodingResponse->json()["features"][0];
                $journey->longitude =
                    $geocodingData["geometry"]["coordinates"][0];
                $journey->latitude =
                    $geocodingData["geometry"]["coordinates"][1];
                $journey->mapbox_full_address =
                    $geocodingData["properties"]["full_address"];
                if ($validated["destination"] === "") {
                    $journey->destination =
                        $geocodingData["properties"]["full_address"];
                }
            }
        }

        $journey->save();

        return response()->json(
            [
                "message" => "Journey updated successfully",
                "journey" => $journey,
            ],
            200
        );
    }

    /**
     * Remove the specified journey from the database.
     */
    public function destroy(Request $request, Journey $journey)
    {
        Gate::authorize("journeyGuide", $journey);

        $journey->delete();
    }
}
