<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Http;
use App\Models\Journey;

class MapboxService
{
    /**
     * Fetch the address details from Mapbox.
     * @param array $validated The validated request data.
     * @param string|null $previousFullAddress The previous mapbox full address.
     * @param string|null $previousDestinationOrAddress The previous destination (in case of a journey) or address (in case of an activity).
     * @return array The updated validated request data.
     */
    public function fetchAddressDetails(
        array $validated,
        $previousFullAddress = null,
        $previousDestinationOrAddress = null,
        Journey $activityOwnerJourney = null
    ): array {
        if (
            isset($validated["mapbox_full_address"]) &&
            $validated["mapbox_full_address"] !== $previousFullAddress
        ) {
            $validated["destination"] = "";
            $validated["address"] = "";
        } elseif (
            (($validated["destination"] ?? $validated["address"] ?? "") !==
                $previousDestinationOrAddress) &&
            (($validated["destination"] ?? $validated["address"] ?? "") !== "")
        ) {
            $searchData = $this->searchMapbox(
                $validated["destination"] ?? ($validated["address"] ?? ""),
                $activityOwnerJourney?->longitude ?? 0,
                $activityOwnerJourney?->latitude ?? 0
            );
            if (isset($searchData["features"][0])) {
                $feature = $searchData["features"][0];
                $validated["mapbox_full_address"] =
                    $feature["properties"]["full_address"] ??
                    $feature["properties"]["name_preferred"] ??
                    $feature["properties"]["name"] ??
                    $feature["properties"]["place_formatted"];
                $validated["mapbox_id"] = $feature["properties"]["mapbox_id"];
            } else {
                $validated["mapbox_full_address"] =
                    $validated["destination"] ?? ($validated["adress"] ?? "");
            }
        }


        return $validated;
    }

    /**
     * Set the geocode data for the journey or activity.
     * @param Journey|Activity $journeyOrActivity The journey or activity.
     * @param array $validated The validated request data.
     * @return Journey|Activity The updated journey or activity.
     */
    public function setGeocodeData(
        Journey|Activity $journeyOrActivity,
        array $validated,
        Journey $activityOwnerJourney = null
    ): Journey|Activity {
        if (
            isset($validated["mapbox_full_address"]) &&
            $validated["mapbox_full_address"]
        ) {
            $geocodingData = $this->geocodeAddress(
                $validated["mapbox_full_address"],
                $activityOwnerJourney?->longitude ?? 0,
                $activityOwnerJourney?->latitude ?? 0
            );
            if (isset($geocodingData["features"][0])) {
                $feature = $geocodingData["features"][0];
                $journeyOrActivity->longitude =
                    $feature["geometry"]["coordinates"][0];
                $journeyOrActivity->latitude =
                    $feature["geometry"]["coordinates"][1];
                $journeyOrActivity->mapbox_full_address =
                    $feature["properties"]["full_address"] ?? "";
                if (
                    empty($validated["destination"]) &&
                    $journeyOrActivity instanceof Journey
                ) {
                    $journeyOrActivity->destination =
                        $journeyOrActivity->mapbox_full_address;
                } elseif (
                    empty($validated["address"]) &&
                    $journeyOrActivity instanceof Activity
                ) {
                    $journeyOrActivity->address =
                        $journeyOrActivity->mapbox_full_address;
                }
            }
        }

        return $journeyOrActivity;
    }

    /**
     * Search for a location on Mapbox.
     */
    private function searchMapbox(
        string $query,
        float $longitude = 0,
        float $latitude = 0
    ): array {
        $response = Http::withHeaders([
            "Origin" => config("app.url"),
            "Referer" => config("app.url"),
        ])->get(
            "https://api.mapbox.com/search/searchbox/v1/forward?q=" .
                urlencode($query) .
                "&proximity=$longitude,$latitude" .
                "&limit=1&access_token=" .
                config("map.mapbox_api_key")
        );

        return $response->json();
    }

    /**
     * Geocode an address on Mapbox.
     */
    private function geocodeAddress(
        string $address,
        float $longitude = 0,
        float $latitude = 0
    ): array {
        $response = Http::withHeaders([
            "Origin" => config("app.url"),
            "Referer" => config("app.url"),
        ])->get(
            "https://api.mapbox.com/search/geocode/v6/forward?q=" .
                urlencode($address) .
                "&proximity=$longitude,$latitude" .
                "&permanent=true&autocomplete=true&limit=1&access_token=" .
                config("map.mapbox_api_key")
        );

        return $response->json();
    }
}
