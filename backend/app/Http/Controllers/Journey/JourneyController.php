<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Journey\StoreJourneyRequest;
use App\Http\Requests\Journey\UpdateJourneyRequest;
use App\Models\JourneyUser;
use App\Services\MapboxService;
use App\Services\WeatherService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class JourneyController extends Controller
{
    /**
     * The Mapbox service.
     */
    protected $mapboxService;
    /**
     * The Weather service.
     */
    protected $weatherService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        MapboxService $mapboxService,
        WeatherService $weatherService
    ) {
        $this->mapboxService = $mapboxService;
        $this->weatherService = $weatherService;
    }

    /**
     * Display all journeys of the authenticated user.
     */
    public function index()
    {
        Gate::authorize("viewAny", [Journey::class, false]);

        /**
         * @disregard P1013 Undefined method
         * This is needed for my PHP plugin to recognize the method
         */
        // Get all journeys of the authenticated user
        $journeys = Auth::user()
            ->journeys()
            ->where("is_template", false)
            ->get(["id", "name", "destination", "from", "to", "role"]);

        return response()->json($journeys);
    }

    /**
     * Store the new journey and add the authenticated user to it.
     */
    public function store(StoreJourneyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $guestMode = !Auth::check();

        // Handle destination and Mapbox address fetching
        $validated = $this->mapboxService->fetchAddressDetails($validated);

        $journey = new Journey($validated);

        // Assign UUID and guest mode if applicable
        if (!$journey->invite) {
            $journey->invite = Str::uuid();
        }

        if ($guestMode) {
            $journey->is_guest = true;
        }

        // Geocoding the full address if it exists
        $journey = $this->mapboxService->setGeocodeData($journey, $validated);

        $journey->save();

        if (!$guestMode) {
            // Add the authenticated user to the journey with the role of 1 (journey guide)
            $journey->users()->attach(Auth::id(), [
                "role" => JourneyUser::JOURNEY_GUIDE_ROLE_ID,
            ]);
        }

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
        Gate::authorize("view", [$journey, true]);

        return response()->json($journey);
    }

    /**
     * Update the specified journey.
     */
    public function update(UpdateJourneyRequest $request, Journey $journey)
    {
        $validated = $request->validated();

        // Handle destination and Mapbox address fetching
        $validated = $this->mapboxService->fetchAddressDetails(
            $validated,
            $journey->mapbox_full_address,
            $journey->destination
        );

        // Update journey data
        $oldMapboxFullAddress = $journey->mapbox_full_address;
        $journey->fill($validated);

        // Reset geocode data if the address has changed
        if (
            $oldMapboxFullAddress !==
            ($validated["mapbox_full_address"] ?? "")
        ) {
            $journey->longitude = null;
            $journey->latitude = null;
            $journey->mapbox_full_address = null;
            $journey = $this->mapboxService->setGeocodeData(
                $journey,
                $validated
            );
        }

        // Save journey
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
    public function destroy(Journey $journey)
    {
        Gate::authorize("delete", [$journey, true]);

        self::deleteJourney($journey);
    }

    /**
     * Delete the journey and its uploads.
     */
    public static function deleteJourney(Journey $journey)
    {
        // Delete journey uploads in folder
        $journeyFolder = storage_path("app/journey_media/" . $journey->id);
        if (file_exists($journeyFolder)) {
            $files = scandir($journeyFolder);
            foreach ($files as $file) {
                if ($file !== "." && $file !== "..") {
                    unlink($journeyFolder . "/" . $file);
                }
            }
            rmdir($journeyFolder);
        }

        $journey->delete();
    }

    /**
     * Get the weather data for the specified journey.
     */
    public function getWeather(Journey $journey)
    {
        Gate::authorize("view", [$journey, true]);

        $weatherDataResponse = $this->weatherService->getWeatherData($journey);

        return response()->json($weatherDataResponse);
    }
}
