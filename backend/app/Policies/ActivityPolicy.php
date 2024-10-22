<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\Journey;
use App\Models\User;
use App\Policies\JourneyPolicy;

class ActivityPolicy
{
    /**
     * The journey policy instance.
     */
    protected $journeyPolicy;

    /**
     * Create a new policy instance.
     */
    public function __construct(JourneyPolicy $journeyPolicy)
    {
        $this->journeyPolicy = $journeyPolicy;
    }

    /**
     * Determine whether the user can view any activities in a given journey.
     */
    public function viewAny(
        ?User $user,
        Journey $journey,
        bool $allowGuests
    ): bool {
        return $this->journeyPolicy->view($user, $journey, $allowGuests);
    }

    /**
     * Determine whether the user can view the activity.
     */
    public function view(
        ?User $user,
        Activity $activity,
        Journey $journey,
        bool $allowGuests
    ): bool {
        if (!$this->journeyPolicy->view($user, $journey, $allowGuests)) {
            return false;
        }

        return $this->activityBelongsToJourney($journey, $activity);
    }

    /**
     * Determine whether the user can create activities.
     */
    public function create(
        ?User $user,
        Journey $journey,
        bool $allowGuests
    ): bool {
        return $this->journeyPolicy->update($user, $journey, $allowGuests);
    }

    /**
     * Determine whether the user can update the activity.
     */
    public function update(
        ?User $user,
        Activity $activity,
        Journey $journey,
        bool $allowGuests
    ): bool {
        if (!$this->journeyPolicy->update($user, $journey, $allowGuests)) {
            return false;
        }

        return $this->activityBelongsToJourney($journey, $activity);
    }

    /**
     * Determine whether the user can delete the activity.
     */
    public function delete(
        ?User $user,
        Activity $activity,
        Journey $journey,
        bool $allowGuests
    ): bool {
        if (!$this->journeyPolicy->update($user, $journey, $allowGuests)) {
            return false;
        }

        return $this->activityBelongsToJourney($journey, $activity);
    }

    /**
     * Determine whether an activity belongs to a journey.
     */
    public function activityBelongsToJourney(
        Journey $journey,
        Activity $activity
    ): bool {
        return $activity->journey_id == $journey->id;
    }
}
