<?php

namespace App\Http\Controllers\Journey\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journey\Activity\CalendarActivity\StoreCalendarActivityRequest;
use App\Http\Requests\Journey\Activity\CalendarActivity\UpdateCalendarActivityRequest;
use App\Models\Activity;
use App\Models\CalendarActivity;
use App\Models\Journey;
use DateTime;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class CalendarActivityController extends Controller
{
    /**
     * Add an activity to the calendar of the journey.
     * Do not remove the journey parameter, it is used for authorization.
     */
    public function store(
        StoreCalendarActivityRequest $request,
        Journey $journey,
        Activity $activity
    ) {
        $validated = $request->validated();

        $calendarActivity = new CalendarActivity($validated);
        $calendarActivity->activity_id = $activity->id;

        $calendarActivity->save();

        return response()->json($calendarActivity, 201);
    }

    /**
     * Update the specified calendar activity in storage.
     * Do not remove the journey and actvitity parameter, they're used for authorization.
     */
    public function update(
        UpdateCalendarActivityRequest $request,
        Journey $journey,
        Activity $activity,
        CalendarActivity $calendarActivity
    ) {
        $validated = $request->validated();

        $calendarActivity->update($validated);

        return response()->json($calendarActivity, 200);
    }

    /**
     * Remove the specified calendar activity from storage.
     */
    public function destroy(
        Request $request,
        Journey $journey,
        Activity $activity,
        CalendarActivity $calendarActivity
    ) {
        Gate::authorize("delete", [
            $calendarActivity,
            $activity,
            $journey,
            true,
        ]);

        $validated = $request->validate([
            "edit_type" => "required|in:single,following,all",
        ]);

        if ($validated["edit_type"] === "single") {
            $activity = $calendarActivity->activity()->first();
            $calendarActivity->delete();
            $this->generalizeActivityIfNeeded($activity);
        } elseif ($validated["edit_type"]) {
            $minDate = $calendarActivity->from;
            $baseActivity = $activity->getBaseActivity();

            foreach ($baseActivity->children()->get() as $child) {
                $this->removeAllCalendarActivitiesAfter($child, $minDate);
            }

            $this->removeAllCalendarActivitiesAfter($baseActivity, $minDate);
        } else {
            $baseActivity = $activity->getBaseActivity();

            foreach ($baseActivity->children()->get() as $child) {
                $this->removeAllCalendarActivities($child);
            }

            $this->removeAllCalendarActivities($baseActivity);
        }

        return response()->json(
            $journey->activities()->with("calendarActivities")->get(),
            200
        );
    }

    /**
     * Remove all calendar activities from an activity which start after a specified date.
     */
    private function removeAllCalendarActivitiesAfter(
        Activity $activity,
        DateTime $minDate
    ) {
        foreach ($activity->calendarActivities()->get() as $calendarActivity) {
            if ($calendarActivity->from >= $minDate) {
                $calendarActivity->delete();
            }
        }

        $this->generalizeActivityIfNeeded($activity);
    }

    /**
     * Remove all calendar activities from an activity.
     */
    private function removeAllCalendarActivities(Activity $activity)
    {
        foreach ($activity->calendarActivities()->get() as $calendarActivity) {
            $calendarActivity->delete();
        }

        $this->generalizeActivityIfNeeded($activity);
    }

    /**
     * Generalize the activity if it has no calendar activities.
     */
    private function generalizeActivityIfNeeded(Activity $activity)
    {
        if ($activity->calendar_activities()->count() === 0) {
            $activity->update([
                "parent_id" => null,
                "repeat_type" => null,
                "repeat_interval" => null,
                "repeat_interval_unit" => null,
                "repeat_on" => null,
                "repeat_end_date" => null,
                "repeat_end_occurrences" => null,
            ]);
        }
    }
}
