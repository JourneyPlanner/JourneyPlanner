<?php

namespace App\Policies;

use App\Models\Journey;
use App\Models\User;

class JourneyPolicy
{
    /**
     * Determine whether the user can view any journeys.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the journey.
     */
    public function view(User $user, Journey $journey): bool
    {
        return $journey->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create journeys.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user is a journey guide.
     * Journey guides have additional permissions in the journey (e.g. change the journey details, delete the journey etc.)
     */
    public function journeyGuide(User $user, Journey $journey): bool
    {
        return $journey->users()->where('user_id', $user->id)->wherePivot('role', 1)->exists();
    }
}
