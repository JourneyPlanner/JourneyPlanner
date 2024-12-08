<?php

namespace App\Http\Controllers\Journey\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journey\Activity\DeleteActivityRequest;
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
        if (!isset($validated["repeat_type"])) {
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
            isset($validated["repeat_type"]) &&
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

        $timeDifference = null;
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
                $validated["calendar_activity_id"] ?? ""
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
        $activities = [];
        if ($validated["edit_type"] == "all") {
            $activity->fill($validated);

            // Reset geocode data if the address has changed
            $this->resetGeocodeDataIfNeeded($activity, $validated);
            $activity->save();
            $this->addTimeDifferenceIfNeeded($activity, $timeDifference);

            // Get the changes to apply to the activities
            $changes = $activity->getChanges();

            // Update the remaining activities
            if ($baseActivity->id !== $activity->id) {
                $baseActivity->update($changes);
                $this->addTimeDifferenceIfNeeded($baseActivity, $timeDifference);
            }
            foreach ($baseActivity->children()->get() as $child) {
                if ($child->id === $activity->id) {
                    continue;
                }
                $child->update($changes);
                $this->addTimeDifferenceIfNeeded($child, $timeDifference);
            }

            // Handle repeating activities
            if ($repeatedChanged) {
                $mappings = $this->calculateDateToActivityMappings(
                    $baseActivity
                );
                // Get the first calendar activity which is
                $firstCalendarActivity = $baseActivity
                    ->calendarActivities()
                    ->orderBy("start")
                    ->first();

                foreach ($baseActivity->children()->get() as $child) {
                    $childFirstCalendarActivity = $child
                        ->calendarActivities()
                        ->orderBy("start")
                        ->first();
                    if (
                        $childFirstCalendarActivity &&
                        $childFirstCalendarActivity->start <
                            $firstCalendarActivity->start
                    ) {
                        $firstCalendarActivity = $childFirstCalendarActivity;
                    }
                }
                $this->deleteAllCalendarActivitiesAfterBaseActivity(
                    $baseActivity,
                    $firstCalendarActivity->start,
                    false,
                    false
                );
                $this->handleRepeatingActivity(
                    $journey,
                    $baseActivity,
                    $firstCalendarActivity,
                    $mappings
                );
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

                    $subActivity->parent_id = null;
                    $activities[] = $subActivity->load("calendarActivities");
                }
            } else {
                // Update the current activitiy, get changes and then apply changes to all activities after this one
                $editedActivity = $this->updateActivitiesAfter(
                    $activity,
                    $baseActivity,
                    $editedCalendarActivity->start,
                    $timeDifference,
                    $validated,
                    true
                );

                if ($timeDifference || $repeatedChanged) {
                    $editedActivity->parent_id = null;
                    $activities[] = $editedActivity->load("calendarActivities");
                    foreach ($baseActivity->children() as $childActivity) {
                        $activities[] = $this->updateActivitiesAfter(
                            $childActivity,
                            $editedActivity,
                            $editedCalendarActivity->start,
                            $timeDifference,
                            $editedActivity->getChanges(),
                            false
                        )->load("calendarActivities");
                    }

                    if ($repeatedChanged) {
                        $mappings = $this->calculateDateToActivityMappings(
                            $editedActivity
                        );
                        $this->deleteAllCalendarActivitiesAfterBaseActivity(
                            $editedActivity,
                            $editedCalendarActivity->start,
                            false,
                            false
                        );
                        $this->handleRepeatingActivity(
                            $journey,
                            $editedActivity,
                            $editedCalendarActivity,
                            $mappings
                        );
                    }
                } else {
                    foreach ($baseActivity->children() as $childActivity) {
                        $this->updateActivitiesAfter(
                            $childActivity,
                            $baseActivity,
                            $editedCalendarActivity->start,
                            $timeDifference,
                            $editedActivity->getChanges(),
                            false
                        );
                    }
                }
            }
        }

        // Delete activities without calendar activities unless they are the base activity
        $this->deleteActivitiesWithoutCalendarActivities($baseActivity);

        // Compare all activities with each other, if they have the same data, merge them
        $this->mergeDuplicateActivities($baseActivity);

        // Replace base activity if it doesn't have any calendar activities
        if (
            !$baseActivity->calendarActivities()->count() &&
            $baseActivity->children()->count()
        ) {
            $replacementActivity = $this->replaceBaseActivity($baseActivity);

            $baseActivity->delete();
            $baseActivity = $replacementActivity;
        }

        // Combine newly created activities with the base activity and its children
        array_push(
            $activities,
            ...$baseActivity->children()->with("calendarActivities")->get()
        );
        $activities[] = $baseActivity->load("calendarActivities");
        return response()->json($activities, 200);
    }

    /**
     * Delete the specified activity from storage.
     */
    public function destroy(
        DeleteActivityRequest $request,
        Journey $journey,
        Activity $activity
    ) {
        Gate::authorize("delete", [$activity, $journey, true]);
        $validated = $request->validated();
        $baseActivity = $activity->getBaseActivity();

        if ($validated["edit_type"] == "all") {
            $baseActivity->children()->delete();
            $baseActivity->delete();

            return response()->json([], 200);
        }

        $calendarActivity = CalendarActivity::findOrFail(
            $validated["calendar_activity_id"]
        );

        if ($validated["edit_type"] == "single") {
            $calendarActivity->delete();
            $this->deleteActivityIfNeeded($activity);
        } elseif ($validated["edit_type"] == "following") {
            $this->deleteAllCalendarActivitiesAfterBaseActivity(
                $activity,
                $calendarActivity->start,
                true,
                true
            );
        }

        $activities = $baseActivity
            ->children()
            ->with("calendarActivities")
            ->get();
        $activities[] = $baseActivity->load("calendarActivities");
        return response()->json($activities, 200);
    }

    /**
     * Add a time difference to the calendar activities of the activity.
     */
    private function addTimeDifferenceIfNeeded(
        Activity $activity,
        ?DateInterval $timeDifference
    ) {
        if ($timeDifference) {
            foreach (
                $activity->calendarActivities()->get()
                as $calendarActivity
            ) {
                $calendarActivity->start = $calendarActivity->start->add(
                    $timeDifference
                );
                $calendarActivity->save();
            }
        }
    }

    /**
     * Calculate the date ranges for the sub activities of the base activity.
     */
    private function calculateDateToActivityMappings(Activity $baseActivity)
    {
        // Create a mapping from date to activity
        $dateToActivityMappings = [];
        foreach (
            $baseActivity->calendarActivities()->get()
            as $calendarActivity
        ) {
            $dateToActivityMappings[
                $calendarActivity->start->format("Y-m-d")
            ] = $baseActivity;
        }
        foreach ($baseActivity->children()->get() as $childActivity) {
            foreach (
                $childActivity->calendarActivities()->get()
                as $calendarActivity
            ) {
                $dateToActivityMappings[
                    $calendarActivity->start->format("Y-m-d")
                ] = $childActivity;
            }
        }

        // Sort the date to activity mappings by date
        ksort($dateToActivityMappings);

        return $dateToActivityMappings;
    }

    /**
     * Delete all calendar activities from an activity which start after a specified date.
     */
    private function deleteAllCalendarActivitiesAfterBaseActivity(
        Activity $baseActivity,
        DateTime $minDate,
        bool $deleteActivities = false,
        bool $includeCurrent = true
    ) {
        foreach ($baseActivity->children()->get() as $child) {
            $this->deleteAllCalendarActivitiesAfter(
                $child,
                $minDate,
                $deleteActivities,
                $includeCurrent
            );
        }

        $this->deleteAllCalendarActivitiesAfter(
            $baseActivity,
            $minDate,
            $deleteActivities,
            $includeCurrent
        );
    }

    /**
     * Delete all calendar activities from an activity which start after a specified date.
     */
    private function deleteAllCalendarActivitiesAfter(
        Activity $activity,
        DateTime $minDate,
        bool $deleteActivities = false,
        bool $includeCurrent = true
    ) {
        foreach ($activity->calendarActivities()->get() as $calendarActivity) {
            if ($includeCurrent && $calendarActivity->start >= $minDate) {
                $calendarActivity->delete();
            } elseif ($calendarActivity->start > $minDate) {
                $calendarActivity->delete();
            }
        }

        if ($deleteActivities) {
            $this->deleteActivityIfNeeded($activity);
        }
    }

    /**
     * Delete the activity if it has no calendar activities.
     */
    private function deleteActivityIfNeeded(Activity $activity)
    {
        if ($activity->calendarActivities()->count() === 0) {
            $activity->delete();
        }
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
    private function handleRepeatingActivity(
        Journey $journey,
        Activity $activity,
        CalendarActivity $calendarActivity,
        array $mappings = []
    ) {
        $mappingsExist = !empty($mappings);
        $calendarActivityStart = $calendarActivity->start;
        $repeatEndDate = $activity->repeat_end_date ?? $journey->to;
        if ($repeatEndDate > $journey->to) {
            $repeatEndDate = $journey->to;
        }
        $repeatEndDate->setTime(23, 59, 59);

        if (
            $activity->repeat_type == "custom" &&
            $activity->repeat_interval_unit == "weeks"
        ) {
            $repeatOn = $activity->repeat_on ?? [];
            $occurences =
                $activity->repeat_end_occurrences ??
                (($calendarActivityStart->diff($repeatEndDate)->d + 1) / 7) *
                    count($repeatOn);
            $shiftInterval = new DateInterval("P1D");
            while ($occurences > 1) {
                $calendarActivityStart->add($shiftInterval);
                if ($calendarActivityStart > $repeatEndDate) {
                    break;
                }
                if (in_array($calendarActivityStart->format("D"), $repeatOn)) {
                    $repeatedActivity = $calendarActivity
                        ->replicate(["start"])
                        ->fill([
                            "start" => $calendarActivityStart,
                        ]);
                    if ($mappingsExist) {
                        $repeatedActivity->activity_id = $this->getActivityFromDateMapping(
                            $mappings,
                            $calendarActivityStart
                        )->id;
                    }
                    $repeatedActivity->save();
                    $occurences--;
                }
                if ($calendarActivityStart->format("D") == "Sun") {
                    $calendarActivityStart->add(
                        new DateInterval(
                            "P" . ($activity->repeat_interval - 1) . "W"
                        )
                    );
                }
            }
        } else {
            $repeatEveryDays =
                $activity->repeat_interval ??
                ($activity->repeat_type == "weekly" ? 7 : 1);
            $occurences =
                $activity->repeat_end_occurrences ??
                ($calendarActivityStart->diff($repeatEndDate)->d + 1) /
                    $repeatEveryDays;
            $shiftInterval = new DateInterval("P" . $repeatEveryDays . "D");

            for ($i = 1; $i < $occurences; $i++) {
                $calendarActivityStart->add($shiftInterval);
                if ($calendarActivityStart > $repeatEndDate) {
                    break;
                }
                $repeatedActivity = $calendarActivity
                    ->replicate(["start"])
                    ->fill([
                        "start" => $calendarActivityStart,
                    ]);
                if ($mappingsExist) {
                    $repeatedActivity->activity_id = $this->getActivityFromDateMapping(
                        $mappings,
                        $calendarActivityStart
                    )->id;
                }
                $repeatedActivity->save();
            }
        }
    }

    /**
     * Get the last activity before the calendar activity
     */
    private function getActivityFromDateMapping(
        array $mappings,
        DateTime $searchDate
    ) {
        $lastActivity = null;
        foreach ($mappings as $date => $activity) {
            if ($date > $searchDate->format("Y-m-d")) {
                break;
            }
            $lastActivity = $activity;
        }

        return $lastActivity;
    }

    /**
     * Replace the current base activity with the activity that has the most calendar activities.
     */
    private function replaceBaseActivity($baseActivity)
    {
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

        $replacementActivity->parent_id = null;
        $replacementActivity->save();

        return $replacementActivity;
    }

    /**
     * Merge duplicate activities.
     */
    private function mergeDuplicateActivities($baseActivity)
    {
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
    }

    /**
     * Delete activities without calendar activities unless they are the base activity.
     */
    private function deleteActivitiesWithoutCalendarActivities($baseActivity)
    {
        $baseActivity
            ->children()
            ->whereDoesntHave("calendarActivities")
            ->where("id", "!=", $baseActivity->id)
            ->delete();
    }

    /**
     * Reset the geocode data if the address has changed.
     */
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

        return $subActivity;
    }
}
