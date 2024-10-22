<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\CalendarActivity;
use App\Models\Journey;
use App\Models\User;

class CalendarActivityPolicy
{
    /**
     * The activity policy instance.
     */
    protected $activityPolicy;

    /**
     * Create a new policy instance.
     */
    public function __construct(ActivityPolicy $activityPolicy)
    {
        $this->activityPolicy = $activityPolicy;
    }

    /**
     * Determine whether the user can create calendar activities.
     */
    public function create(
        ?User $user,
        Activity $activity,
        Journey $journey,
        bool $allowGuests
    ): bool {
        return $this->activityPolicy->update(
            $user,
            $activity,
            $journey,
            $allowGuests
        );
    }

    /**
     * Determine whether the user can update the calendar activity.
     */
    public function update(
        ?User $user,
        CalendarActivity $calendarActivity,
        Activity $activity,
        Journey $journey,
        bool $allowGuests
    ): bool {
        if (
            !$this->activityPolicy->update(
                $user,
                $activity,
                $journey,
                $allowGuests
            )
        ) {
            return false;
        }

        return $this->calendarActivityBelongsToJourney(
            $journey,
            $activity,
            $calendarActivity
        );
    }

    /**
     * Determine whether the user can delete the calendar activity.
     */
    public function delete(
        ?User $user,
        CalendarActivity $calendarActivity,
        Activity $activity,
        Journey $journey,
        bool $allowGuests
    ): bool {
        if (
            !$this->activityPolicy->update(
                $user,
                $activity,
                $journey,
                $allowGuests
            )
        ) {
            return false;
        }

        return $this->calendarActivityBelongsToJourney(
            $journey,
            $activity,
            $calendarActivity
        );
    }

    /**
     * Determine whether a calendar activity belongs to a journey.
     */
    public function calendarActivityBelongsToJourney(
        Journey $journey,
        Activity $activity,
        CalendarActivity $calendarActivity
    ): bool {
        if (
            !$this->activityPolicy->activityBelongsToJourney(
                $journey,
                $activity
            )
        ) {
            return false;
        }

        return $calendarActivity->activity_id === $activity->id;
    }
}
