<?php

namespace App\Http\Controllers\Journey\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journey\Activity\CalendarActivity\StoreCalendarActivityRequest;
use App\Http\Requests\Journey\Activity\CalendarActivity\UpdateCalendarActivityRequest;
use App\Models\Activity;
use App\Models\CalendarActivity;
use App\Models\Journey;
use Illuminate\Support\Facades\Gate;

class CalendarActivityController extends Controller
{
    /**
     * Add an activity to the calendar of the journey.
     */
    public function store(
        StoreCalendarActivityRequest $request,
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
     */
    public function update(
        UpdateCalendarActivityRequest $request,
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

        $calendarActivity->delete();
    }
}
