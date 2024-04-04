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
     * Determine whether the user can update the journey.
     */
    public function update(User $user, Journey $journey): bool
    {
        return $journey->users()->where('user_id', $user->id)->wherePivot('role', 1)->exists();
    }

    /**
     * Determine whether the user can delete the journey.
     */
    public function delete(User $user, Journey $journey): bool
    {
        return $journey->users()->where('user_id', $user->id)->wherePivot('role', 1)->exists();
    }

    /**
     * Determine whether the user can restore the journey.
     */
    public function restore(User $user, Journey $journey): bool
    {
        return $journey->users()->where('user_id', $user->id)->wherePivot('role', 1)->exists();
    }

    /**
     * Determine whether the user can permanently delete the journey.
     */
    public function forceDelete(User $user, Journey $journey): bool
    {
        return $journey->users()->where('user_id', $user->id)->wherePivot('role', 1)->exists();
    }
}
