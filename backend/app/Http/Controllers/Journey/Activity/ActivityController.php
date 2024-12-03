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
            $activity["calendar_activities"] = [];
            return response()->json($activity, 201);
        }

        // Handle repeating activities
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

        $this->handleRepeatingActivity($journey, $activity, $calendarActivity);

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

        $repeatedChanged =
            array_key_exists("repeat_type", $validated) &&
            $validated["repeat_type"] &&
            (($activity->repeat_type ?? "") != $validated["repeat_type"] ||
                ($activity->repeat_interval ?? 0) !=
                    ($validated["repeat_interval"] ?? 0) ||
                ($activity->repeat_interval_unit ?? "") !=
                    ($validated["repeat_interval_unit"] ?? "") ||
                ($activity->repeat_on ?? []) !=
                    ($validated["repeat_on"] ?? []) ||
                ($activity->repeat_end_date ?? "") !=
                    ($validated["repeat_end_date"] ?? "") ||
                ($activity->repeat_end_occurrences ?? 0) !=
                    ($validated["repeat_end_occurrences"] ?? 0));
        //ddd($repeatedChanged);

        // Create the calendar activity if the date is provided and the activity is not repeated and has no calendar activities
        if ($activity->calendarActivities()->count() === 0) {
            $calendarActivityCreated = $this->createCalendarActivityIfNeeded(
                $validated,
                $activity
            );

            if ($calendarActivityCreated && $activity->repeat_type != null) {
                $calendarActivity = $activity->calendarActivities()->first();

                if ($calendarActivity) {
                    $this->handleRepeatingActivity(
                        $journey,
                        $activity,
                        $calendarActivity
                    );
                }
            }
        } elseif (isset($validated["date"])) {
            $editedCalendarActivity = CalendarActivity::findOrFail(
                $validated["calendar_activity_id"]
            );

            if (!isset($validated["time"])) {
                $validated["time"] = "00:00:00";
            }

            $newStart = new DateTime(
                $validated["date"] . " " . $validated["time"]
            );

            $timeDifference = $editedCalendarActivity->start->diff($newStart);
        }

        // Edit repeated activities
        $baseActivity = $activity->getBaseActivity();
        if ($validated["edit_type"] == "all") {
            $activity->fill($validated);

            // Reset geocode data if the address has changed
            $this->resetGeocodeDataIfNeeded($activity, $validated);
            $activity->save();

            $changes = $activity->getChanges();

            if ($timeDifference) {
                foreach (
                    $baseActivity->calendarActivities()->get()
                    as $calendarActivity
                ) {
                    $calendarActivity->start = $calendarActivity->start->add(
                        $timeDifference
                    );
                    $calendarActivity->save();
                }
            }

            foreach ($baseActivity->children()->get() as $child) {
                $child->fill($changes);
                $child->save();
                if ($timeDifference) {
                    foreach (
                        $child->calendarActivities()->get()
                        as $calendarActivity
                    ) {
                        $calendarActivity->start = $calendarActivity->start->add(
                            $timeDifference
                        );
                        $calendarActivity->save();
                    }
                }
            }
        } else {
            $editedCalendarActivity = CalendarActivity::findOrFail(
                $validated["calendar_activity_id"]
            );

            Gate::authorize("update", [
                $editedCalendarActivity,
                $activity,
                $journey,
                true,
            ]);

            if ($validated["edit_type"] == "single") {
                $subActivity = $activity->replicate();
                $subActivity->fill($validated);
                $this->resetGeocodeDataIfNeeded($activity, $validated);
                $subActivity->parent_id = $baseActivity->id;
                $subActivity->save();

                $editedCalendarActivity->activity_id = $subActivity->id;
                $editedCalendarActivity->save();

                if ($timeDifference) {
                    $editedCalendarActivity->start = $editedCalendarActivity->start->add(
                        $timeDifference
                    );
                    $editedCalendarActivity->save();
                }
            } else {
                // Update the current activitiy, get changes and then apply changes to all activities after this one
                $changes = $this->updateActivitiesAfter(
                    $activity,
                    $baseActivity,
                    $editedCalendarActivity->start,
                    $timeDifference,
                    $validated,
                    true
                );

                foreach ($baseActivity->children() as $childActivity) {
                    $this->updateActivitiesAfter(
                        $childActivity,
                        $baseActivity,
                        $editedCalendarActivity->start,
                        $timeDifference,
                        $changes,
                        false
                    );
                }
            }
        }

        // Delete activities without calendar activities unless they are the base activity
        $baseActivity
            ->children()
            ->whereDoesntHave("calendarActivities")
            ->where("id", "!=", $baseActivity->id)
            ->delete();

        // Compare all activities with each other, if they have the same data, merge them
        foreach ($baseActivity->children()->get() as $childActivity) {
            if ($childActivity->hasSameAttributesAs($baseActivity)) {
                $childActivity
                    ->calendarActivities()
                    ->update(["activity_id" => $baseActivity->id]);
                $childActivity->delete();
            }
        }

        foreach ($baseActivity->children()->get() as $childActivity) {
            foreach ($baseActivity->children()->get() as $siblingActivity) {
                if (
                    $childActivity->hasSameAttributesAs($siblingActivity) &&
                    $childActivity->id != $siblingActivity->id
                ) {
                    $siblingActivity
                        ->calendarActivities()
                        ->update(["activity_id" => $childActivity->id]);
                    $siblingActivity->delete();
                }
            }
        }

        // Replace base activity if it doesn't have any calendar activities
        if (
            !$baseActivity->calendarActivities()->count() &&
            $baseActivity->children()->count()
        ) {
            $replacementActivity = $baseActivity->children()->first();
            $mostCalendarActivities = $replacementActivity
                ->calendarActivities()
                ->count();
            foreach ($baseActivity->children()->get() as $childActivity) {
                if (
                    $childActivity->calendarActivities()->count() >
                    $mostCalendarActivities
                ) {
                    $replacementActivity = $childActivity;
                    $mostCalendarActivities = $childActivity
                        ->calendarActivities()
                        ->count();
                }
            }

            foreach ($baseActivity->children()->get() as $childActivity) {
                $childActivity->parent_id = $replacementActivity->id;
                $childActivity->save();
            }

            $baseActivity->delete();
            $replacementActivity->parent_id = null;
            $replacementActivity->save();

            $baseActivity = $replacementActivity;
        }

        $activities = $baseActivity
            ->children()
            ->with("calendarActivities")
            ->get();
        $activities[] = $baseActivity->load("calendarActivities");
        return response()->json($activities, 201);
    }

    private function resetGeocodeDataIfNeeded(
        Activity $activity,
        array $validated
    ) {
        if ($activity->isDirty("mapbox_full_address")) {
            $activity->longitude = null;
            $activity->latitude = null;
            $activity->mapbox_full_address = null;
            $activity = $this->mapboxService->setGeocodeData(
                $activity,
                $validated
            );
        }
    }

    /**
     * Update the activities after the date of the edited calendar activity.
     */
    private function updateActivitiesAfter(
        Activity $activity,
        Activity $baseActivity,
        DateTime $editedCalendarActivityStart,
        ?DateInterval $timeDifference,
        array $changes,
        bool $reencode
    ) {
        $calendarActivities = $activity->calendarActivities()->get();
        $subActivity = $activity->replicate();
        $subActivity->save();
        $subActivity->fill($changes);
        $subActivity->parent_id = $baseActivity->id;

        if ($reencode) {
            $this->resetGeocodeDataIfNeeded($activity, $changes);
        }
        $subActivity->save();

        foreach ($calendarActivities as $calendarActivity) {
            if ($calendarActivity->start >= $editedCalendarActivityStart) {
                $calendarActivity->activity_id = $subActivity->id;
                if ($timeDifference) {
                    $calendarActivity->start = $calendarActivity->start->add(
                        $timeDifference
                    );
                }
                $calendarActivity->save();
            }
        }

        return $subActivity->getChanges();
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
        if (!isset($validated["date"])) {
            return false;
        }

        if (!isset($validated["time"])) {
            $validated["time"] = "00:00:00";
        }

        $start = new DateTime($validated["date"] . " " . $validated["time"]);

        $calendarActivity = new CalendarActivity([
            "activity_id" => $activity->id,
            "start" => $start,
        ]);
        $calendarActivity->save();

        return true;
    }

    /**
     * Repeat the activity if needed.
     */
    public static function handleRepeatingActivity(
        Journey $journey,
        Activity $activity,
        CalendarActivity $calendarActivity
    ) {
        $calendarActivityStart = $calendarActivity->start;
        $maxDate = $journey->to;
        $maxDate->setTime(23, 59, 59);
        $repeatEndDate = $activity->repeat_end_date ?? $journey->to;
        if ($repeatEndDate > $maxDate) {
            $repeatEndDate = $maxDate;
        }

        if (
            $activity->repeat_type == "custom" &&
            $activity->repeat_interval_unit == "weeks"
        ) {
            $repeatOn = $activity->repeat_on ?? [];
            $occurences =
                $activity->repeat_end_occurrences ??
                (($calendarActivityStart->diff($maxDate)->days + 1) / 7) *
                    count($repeatOn);
            $shiftInterval = new DateInterval("P1D");
            while ($occurences > 1) {
                $calendarActivityStart->add($shiftInterval);
                if ($calendarActivityStart > $maxDate) {
                    break;
                }
                if (in_array($calendarActivityStart->format("D"), $repeatOn)) {
                    $repeatedActivity = $calendarActivity
                        ->replicate(["start"])
                        ->fill([
                            "start" => $calendarActivityStart,
                        ]);
                    $repeatedActivity->save();
                    $occurences--;
                }
                if ($calendarActivityStart->format("D") == "Sun") {
                    $shift = $activity->repeat_interval - 1;
                    $calendarActivityStart->add(
                        new DateInterval("P" . $shift . "W")
                    );
                }
            }
        } else {
            $repeatEveryDays =
                $activity->repeat_interval ??
                ($activity->repeat_type == "weekly" ? 7 : 1);
            $occurences =
                $activity->repeat_end_occurrences ??
                ($calendarActivityStart->diff($maxDate)->days + 1) /
                    $repeatEveryDays;
            $shiftInterval = new DateInterval("P" . $repeatEveryDays . "D");

            for ($i = 1; $i < $occurences; $i++) {
                $calendarActivityStart->add($shiftInterval);
                if ($calendarActivityStart > $maxDate) {
                    break;
                }
                $repeatedActivity = $calendarActivity
                    ->replicate(["start"])
                    ->fill([
                        "start" => $calendarActivityStart,
                    ]);
                $repeatedActivity->save();
            }
        }
    }
}
