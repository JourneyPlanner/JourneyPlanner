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
use DateInterval;
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
            ->get([
                "id",
                "name",
                "destination",
                "mapbox_full_address",
                "mapbox_id",
                "from",
                "to",
                "role",
            ]);

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

        if (isset($validated["template_id"])) {
            $journey->created_from = $validated["template_id"];
            $journey->save();

            // Copy the activities from the template journey
            $templateJourney = Journey::where("id", $validated["template_id"])
                ->where("is_template", true)
                ->firstOrFail();
            // Calculate the time difference between template start and new journey start
            $timeshift = $templateJourney->from->diff($journey->from);
            // Calculate journey length in weeks, rounding up partial weeks
            $journeyLength = ceil($journey->from->diff($journey->to)->d / 7);
            $endDate = $journey->to;
            $endDate->setTime(23, 59, 59);
            if ($validated["calendar_activity_insert_mode"] === "smart") {
                // Calculate additional days needed to align weekdays between template and new journey
                $additionalWeekDayShift =
                    $templateJourney->from->format("N") -
                    $journey->from->format("N");
                if ($additionalWeekDayShift < 0) {
                    $additionalWeekDayShift += 7;
                }
            }
            foreach (
                $templateJourney
                    ->activities()
                    ->with("calendarActivities")
                    ->get()
                as $activity
            ) {
                $newActivity = $activity->replicate();
                $newActivity->journey_id = $journey->id;
                $newActivity->save();

                // Copy the calendar activities
                if ($validated["calendar_activity_insert_mode"] === "direct") {
                    foreach (
                        $activity->calendarActivities
                        as $calendarActivity
                    ) {
                        $newDate = $calendarActivity->start->add($timeshift);
                        if ($newDate <= $endDate) {
                            $newCalendarActivity = $calendarActivity->replicate();
                            $newCalendarActivity->activity_id =
                                $newActivity->id;
                            $newCalendarActivity->start = $newDate;
                            $newCalendarActivity->save();
                        }
                    }
                } elseif (
                    $validated["calendar_activity_insert_mode"] === "smart"
                ) {
                    foreach (
                        $activity->calendarActivities
                        as $calendarActivity
                    ) {
                        $newDate = $calendarActivity->start
                            ->add($timeshift)
                            ->add(
                                new DateInterval(
                                    "P" . $additionalWeekDayShift . "D"
                                )
                            );
                        $newCalendarActivity = $calendarActivity->replicate();
                        $newCalendarActivity->activity_id = $newActivity->id;

                        if ($newDate > $endDate) {
                            if (
                                $journey->to->diff($newDate)->d <=
                                $additionalWeekDayShift + 1
                            ) {
                                $newDate = $newDate->sub(
                                    new DateInterval("P" . $journeyLength . "W")
                                );
                            } else {
                                continue;
                            }
                        }

                        if ($newDate > $journey->from) {
                            $newCalendarActivity->start = $newDate;
                            $newCalendarActivity->save();
                        }
                    }
                }
            }
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
