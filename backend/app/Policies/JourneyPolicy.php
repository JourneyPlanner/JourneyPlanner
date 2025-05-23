<?php

namespace App\Policies;

use App\Models\Journey;
use App\Models\JourneyUser;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

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
    public function view(
        ?User $user,
        Journey $journey,
        bool $allowGuests,
        ?string $share_id = null
    ): bool {
        return ($allowGuests && $this->guestJourney($journey)) ||
            $this->journeyTemplate($user, $journey) ||
            ($share_id && $journey->share_id == $share_id) ||
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
        bool $allowGuests,
        bool $forceTemplate = false
    ): bool {
        return (($allowGuests && $this->guestJourney($journey)) ||
            ($user && $this->journeyGuide($user, $journey))) &&
            ($forceTemplate ? $this->journeyTemplate($user, $journey) : true);
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
            ->where(function ($query) {
                $query
                    ->where("role", JourneyUser::JOURNEY_GUIDE_ROLE_ID)
                    ->orWhere("role", JourneyUser::TEMPLATE_CREATOR_ROLE_ID);
            })
            ->exists() || $this->templateBusinessMember($journey);
    }

    /**
     * Determine whether the user is a member of the business which owns the template.
     */
    private function templateBusinessMember(Journey $journey): bool
    {
        $business = $journey
            ->businesses()
            ->wherePivot("created_by_business", true)
            ->first();
        if (!$business) {
            return false;
        }
        return Gate::allows("update", $business);
    }

    private function guestJourney(Journey $journey): bool
    {
        return $journey->is_guest;
    }

    public function journeyTemplate(?User $user, Journey $journey): bool
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
