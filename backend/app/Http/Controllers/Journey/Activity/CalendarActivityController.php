<?php

namespace App\Http\Controllers\Journey\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journey\Activity\CalendarActivity\DeleteCalendarActivityRequest;
use App\Http\Requests\Journey\Activity\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\CalendarActivity;
use App\Models\Journey;
use DateTime;
use Illuminate\Support\Facades\DB;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\UrlParam;

/**
 * @group Activity
 *
 * @subgroup CalendarActivity
 *
 * @subGroupDescription APIs for managing calendar activities.
 *
 * Calendar activities are the instances of activities that are scheduled on a calendar.
 */
class CalendarActivityController extends Controller
{
    /**
     * Delete calendar activity
     *
     * Remove the specified calendar activity from the calendar.
     */
    #[UrlParam('journey_id', description: 'The ID of the journey.')]
    #[UrlParam('activity_id', description: 'The ID of the activity.')]
    #[UrlParam('id', description: 'The ID of the calendar activity.')]
    #[ResponseFromFile('storage/responses/journey/activity/calendarActivity/destroy.200.json', status: 200, description: 'Success. Returns the remaining activities with their calendar activities.')]
    #[ResponseFromFile('storage/responses/journey/activity/calendarActivity/destroy.403.json', status: 403, description: 'Forbidden.')]
    #[ResponseFromFile('storage/responses/journey/activity/calendarActivity/destroy.404.json', status: 404, description: 'Journey, activity or calendar activity not found.')]
    #[ResponseFromFile('storage/responses/journey/activity/calendarActivity/destroy.422.json', status: 422, description: 'Validation Error.')]
    #[ResponseField('message', description: 'The error message.')]
    #[ResponseField('errors', description: 'The validation error messages.')]
    public function destroy(
        DeleteCalendarActivityRequest $request,
        Journey $journey,
        Activity $activity,
        CalendarActivity $calendarActivity
    ) {
        $validated = $request->validated();
        $baseActivity = $activity->getBaseActivity();
        $emptyActivities = [];

        if (
            $validated['edit_type'] === UpdateActivityRequest::EDIT_TYPE_SINGLE
        ) {
            $calendarActivity->delete();
            $emptyActivities = $this->generalizeActivityIfNeeded(
                $activity,
                $emptyActivities
            );
        } elseif (
            $validated['edit_type'] ===
            UpdateActivityRequest::EDIT_TYPE_FOLLOWING
        ) {
            $minDate = $calendarActivity->start;

            foreach ($baseActivity->children()->get() as $child) {
                $emptyActivities = $this->removeAllCalendarActivitiesAfter(
                    $child,
                    $minDate,
                    $emptyActivities
                );
            }

            $emptyActivities = $this->removeAllCalendarActivitiesAfter(
                $baseActivity,
                $minDate,
                $emptyActivities
            );
        } else {
            foreach ($baseActivity->children()->get() as $child) {
                $emptyActivities = $this->removeAllCalendarActivities(
                    $child,
                    $emptyActivities
                );
            }

            $emptyActivities = $this->removeAllCalendarActivities(
                $baseActivity,
                $emptyActivities
            );
        }

        $activities = $baseActivity
            ->children()
            ->with('calendarActivities')
            ->get();
        $activities[] = $baseActivity->load('calendarActivities');
        $activities = $activities->merge($emptyActivities);

        return response()->json($activities, 200);
    }

    /**
     * Remove all calendar activities from an activity which start after a specified date.
     */
    private function removeAllCalendarActivitiesAfter(
        Activity $activity,
        DateTime $minDate,
        $emptyActivities
    ) {
        $activity
            ->calendarActivities()
            ->where('start', '>=', $minDate)
            ->delete();

        return $this->generalizeActivityIfNeeded($activity, $emptyActivities);
    }

    /**
     * Remove all calendar activities from an activity.
     */
    private function removeAllCalendarActivities(
        Activity $activity,
        array $emptyActivities = []
    ) {
        $activity->calendarActivities()->delete();

        return $this->generalizeActivityIfNeeded($activity, $emptyActivities);
    }

    /**
     * Generalize the activity if it has no calendar activities.
     */
    private function generalizeActivityIfNeeded(
        Activity $activity,
        array $emptyActivities = []
    ) {
        return DB::transaction(function () use ($activity, $emptyActivities) {
            if ($activity->calendarActivities()->count() === 0) {
                $activity = ActivityController::resetRepeat($activity);
                $activity->parent_id = null;
                $activity->save();

                $emptyActivities[] = $activity;
            }

            return $emptyActivities;
        });
    }
}
