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

        return response()->json(
            $journey->activities()->with("calendarActivities")->get()
        );
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
        if ($journey->is_guest && $journey->activities()->count() >= 100) {
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
        if (!$this->createCalendarActivityIfNeeded($validated, $activity)) {
            return response()->json($activity, 201);
        }

        // Handle repeating activities
        return $this->handleRepeatingActivity($journey, $activity, $validated);
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
    ): bool {
        if (!array_key_exists("date", $validated) || !$validated["date"]) {
            return false;
        }

        if (!array_key_exists("time", $validated) || !$validated["time"]) {
            $validated["time"] = "00:00:00";
        }

        $start = new DateTime($validated["date"] . " " . $validated["time"]);
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

        return true;
    }

    /**
     * Repeat the activity if needed.
     */
    private function handleRepeatingActivity(
        Journey $journey,
        Activity $activity,
        array $validated
    ): JsonResponse {
        if (
            !array_key_exists("repeat_type", $validated) ||
            !$validated["repeat_type"]
        ) {
            return response()->json($activity->load("calendarActivities"), 201);
        }

        $calendarActivity = $activity->calendarActivities()->first();
        if (!$calendarActivity) {
            return response()->json($activity, 201);
        }
        $calendarActivity->is_repeat_series_starter = true;
        $calendarActivity->save();

        $calendarActivityStart = new DateTime($calendarActivity->start);
        $calendarActivityEnd = new DateTime($calendarActivity->end);
        $repeatEndDate = new DateTime(
            $validated["repeat_end_date"] ?? $journey->to
        );
        if ($repeatEndDate > new DateTime($journey->to)) {
            $repeatEndDate = new DateTime($journey->to);
        }

        if (
            $validated["repeat_type"] == "custom" &&
            $validated["repeat_interval_unit"] == "weeks"
        ) {
            $repeatOn = $validated["repeat_on"] ?? [];
            $occurences =
                $validated["repeat_end_occurrences"] ??
                (($calendarActivityStart->diff($repeatEndDate)->days + 1) / 7) *
                count($repeatOn);
            $shiftInterval = new DateInterval("P1D");
            while ($occurences > 1) {
                $calendarActivityStart->add($shiftInterval);
                $calendarActivityEnd->add($shiftInterval);
                if ($calendarActivityStart > new DateTime($journey->to)) {
                    break;
                }
                if (in_array($calendarActivityStart->format("D"), $repeatOn)) {
                    $repeatedActivity = $calendarActivity
                        ->replicate(["start", "end"])
                        ->fill([
                            "start" => $calendarActivityStart,
                            "end" => $calendarActivityEnd,
                        ]);
                    $repeatedActivity->save();
                    $occurences--;
                }
                if ($calendarActivityStart->format("D") == "Sun") {
                    $shift = $validated["repeat_interval"] - 1;
                    $calendarActivityStart->add(
                        new DateInterval("P" . $shift . "W")
                    );
                    $calendarActivityEnd->add(
                        new DateInterval("P" . $shift . "W")
                    );
                }
            }
        } else {
            $repeatEveryDays =
                $validated["repeat_interval"] ??
                ($validated["repeat_type"] == "weekly" ? 7 : 1);
            $occurences =
                $validated["repeat_end_occurrences"] ??
                ($calendarActivityStart->diff($repeatEndDate)->days + 1) /
                $repeatEveryDays;
            $shiftInterval = new DateInterval("P" . $repeatEveryDays . "D");

            for ($i = 1; $i < $occurences; $i++) {
                $calendarActivityStart->add($shiftInterval);
                $calendarActivityEnd->add($shiftInterval);
                if ($calendarActivityStart > new DateTime($journey->to)) {
                    break;
                }
                $repeatedActivity = $calendarActivity
                    ->replicate(["start", "end"])
                    ->fill([
                        "start" => $calendarActivityStart,
                        "end" => $calendarActivityEnd,
                    ]);
                $repeatedActivity->save();
            }
        }
        return response()->json($activity->load("calendarActivities"), 201);
    }
}
