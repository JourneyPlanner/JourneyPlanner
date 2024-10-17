<?php

namespace App\Http\Requests\Journey\Activity\CalendarActivity;

use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class UpdateCalendarActivityRequest extends StoreCalendarActivityRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize("update", [
            $this->calendarActivity,
            $this->activity,
            $this->journey,
            true,
        ]);
    }
}
