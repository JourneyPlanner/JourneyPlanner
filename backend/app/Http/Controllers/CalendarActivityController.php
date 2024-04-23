<?php

namespace App\Http\Controllers;

use App\Models\CalendarActivity;
use Illuminate\Http\Request;
use App\Models\Journey;
use Illuminate\Support\Facades\Gate;

class CalendarActivityController extends Controller
{
    /**
     * Add an activity to the calendar of the journey.
     */
    public function store(Request $request, $journey, $activity)
    {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyGuide", $journey);

        $activity = $journey->activities()->findOrFail($activity);

        $validated = $request->validate([
            "start" => "required|dateTime",
            "end" => "required|dateTime",
        ]);

        $calendarActivity = new CalendarActivity($validated);
        $calendarActivity->activity_id = $activity->id;

        $calendarActivity->save();

        return response()->json($calendarActivity, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        $journey,
        $activity,
        CalendarActivity $calendarActivity
    ) {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyGuide", $journey);

        $validated = $request->validate([
            "start" => "required|dateTime",
            "end" => "required|dateTime",
        ]);

        $calendarActivity->update($validated);

        return response()->json($calendarActivity, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        $journey,
        $activity,
        CalendarActivity $calendarActivity
    ) {
        $journey = Journey::findOrFail($journey);
        Gate::authorize("journeyGuide", $journey);

        $calendarActivity->delete();

        return response()->json("Calendar activity successfully deleted", 200);
    }
}
