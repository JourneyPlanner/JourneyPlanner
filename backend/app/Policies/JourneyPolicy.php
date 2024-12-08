<?php

namespace App\Policies;

use App\Models\Journey;
use App\Models\User;

class JourneyPolicy
{
    /**
     * Determine whether the user can view any journeys.
     */
    public function viewAny(?User $user, bool $allowGuests): bool
    {
        return $allowGuests || $user;
    }

    /**
     * Determine whether the user can view the journey.
     */
    public function view(?User $user, Journey $journey, bool $allowGuests): bool
    {
        return ($allowGuests && $this->guestJourney($journey)) ||
            $this->journeyTemplate($journey) ||
            ($user && $this->journeyMember($user, $journey));
    }

    /**
     * Determine whether the user can create journeys.
     */
    public function create(?User $user, bool $allowGuests): bool
    {
        return $allowGuests || $user;
    }

    /**
     * Determine whether the user can update the journey.
     */
    public function update(
        ?User $user,
        Journey $journey,
        bool $allowGuests
    ): bool {
        return ($allowGuests && $this->guestJourney($journey)) ||
            ($user && $this->journeyGuide($user, $journey));
    }

    /**
     * Determine whether the user can permanently delete the journey.
     */
    public function delete(
        ?User $user,
        Journey $journey,
        bool $allowGuests
    ): bool {
        return ($allowGuests && $this->guestJourney($journey)) ||
            ($user && $this->journeyGuide($user, $journey));
    }

    /**
     * Determine whether the user is a journey member.
     */
    private function journeyMember(User $user, Journey $journey): bool
    {
        return $journey
            ->users()
            ->where("user_id", $user->id)
            ->exists();
    }

    /**
     * Determine whether the user is a journey guide.
     * Journey guides have additional permissions in the journey (e.g. change the journey details, delete the journey etc.)
     */
    private function journeyGuide(User $user, Journey $journey): bool
    {
        return $journey
            ->users()
            ->where("user_id", $user->id)
            ->wherePivot("role", 1)
            ->exists();
    }

    private function guestJourney(Journey $journey): bool
    {
        return $journey->is_guest;
    }

    private function journeyTemplate(Journey $journey): bool
    {
        return $journey->is_template;
    }

    /**
     * Determine whether a user is a template creator.
     */
    public function journeyTemplateCreator(User $user, Journey $journey): bool
    {
        $templates = $journey->templates()->get();
        foreach ($templates as $template) {
            if (
                $template
                    ->users()
                    ->where("user_id", $user->id)
                    ->wherePivot("role", 2)
                    ->exists()
            ) {
                return true;
            }
        }
        return false;
    }
}
