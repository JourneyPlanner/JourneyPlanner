<?php

namespace App\Http\Controllers\Journey\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journey\Activity\StoreActivityRequest;
use App\Http\Requests\Journey\Activity\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\CalendarActivity;
use App\Models\Journey;
use App\Services\MapboxService;
use DateInterval;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class ActivityController extends Controller
{
    /**
     * The Mapbox service.
     */
    protected $mapboxService;

    /**
     * Create a new controller instance.
     */
    public function __construct(MapboxService $mapboxService)
    {
        $this->mapboxService = $mapboxService;
    }

    /**
     * Get all activities of the specified journey.
     */
    public function index(Journey $journey)
    {
        Gate::authorize("viewAny", [Activity::class, $journey, true]);

        $activities = $journey->activities()->with("calendarActivities")->get();

        return response()->json([
            "activities" => $activities,
            "count" => $activities->count(),
        ]);
    }

    /**
     * Store a newly created activity in storage.
     */
    public function store(
        StoreActivityRequest $request,
        Journey $journey
    ): JsonResponse {
        // Validate the request
        $validated = $request->validated();
        // Limit the number of activities for guests
        if ($journey->is_guest && $journey->activities()->count() >= 10) {
            abort(403, "You have reached the maximum number of activities.");
        }

        // Handle destination and Mapbox address fetching
        $validated = $this->mapboxService->fetchAddressDetails($validated);

        // Create the activity
        $activity = new Activity($validated);
        $activity->journey_id = $journey->id;

        // Get the longitude and latitude of the address if it exists
        $activity = $this->mapboxService->setGeocodeData($activity, $validated);

        $activity->save();

        // Create the calendar activity if the date is provided
        $this->createCalendarActivityIfNeeded($validated, $activity);

        return response()->json($activity->load("calendarActivities"), 201);
    }

    /**
     * Display the specified activity.
     */
    public function show(Journey $journey, Activity $activity)
    {
        Gate::authorize("view", [$activity, $journey, true]);

        return response()->json($activity->load("calendarActivities"));
    }

    /**
     * Update the specified activity in storage.
     * Do not remove the journey parameter, it is required for authorization.
     */
    public function update(
        UpdateActivityRequest $request,
        Journey $journey,
        Activity $activity
    ) {
        // Validate the request
        $validated = $request->validated();

        $validated = $this->mapboxService->fetchAddressDetails(
            $validated,
            $activity->mapbox_full_address,
            $activity->address
        );

        // Update the activity
        $oldMapboxFullAddress = $activity->mapbox_full_address;
        $activity->fill($validated);

        // Reset geocode data if the address has changed
        if (
            $oldMapboxFullAddress !==
            ($validated["mapbox_full_address"] ?? "")
        ) {
            $activity->longitude = null;
            $activity->latitude = null;
            $activity->mapbox_full_address = null;
            $activity = $this->mapboxService->setGeocodeData(
                $activity,
                $validated
            );
        }

        $activity->save();

        // Create the calendar activity if the date is provided
        $this->createCalendarActivityIfNeeded($validated, $activity);

        return response()->json($activity->load("calendarActivities"), 201);
    }

    /**
     * Delete the specified activity from storage.
     */
    public function destroy(Journey $journey, Activity $activity)
    {
        Gate::authorize("delete", [$activity, $journey, true]);

        $activity->delete();
    }

    /**
     * Create a calendar activity if the date is provided.
     */
    private function createCalendarActivityIfNeeded(
        array $validated,
        Activity $activity
    ): void {
        if (array_key_exists("date", $validated) && $validated["date"]) {
            if (!array_key_exists("time", $validated) || !$validated["time"]) {
                $validated["time"] = "00:00:00";
            }

            $start = new DateTime(
                $validated["date"] . " " . $validated["time"]
            );
            $end = clone $start;
            $end->add(
                new DateInterval(
                    "PT" .
                        substr($validated["estimated_duration"], 0, 2) .
                        "H" .
                        substr($validated["estimated_duration"], 3, 2) .
                        "M" .
                        substr($validated["estimated_duration"], 6) .
                        "S"
                )
            );

            $calendarActivity = new CalendarActivity([
                "activity_id" => $activity->id,
                "start" => $start,
                "end" => $end,
            ]);
            $calendarActivity->save();
        }
    }
}
