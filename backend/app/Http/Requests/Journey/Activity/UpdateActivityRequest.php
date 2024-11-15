<?php

namespace App\Http\Requests\Journey\Activity;

use App\Http\Requests\Journey\Activity\StoreActivityRequest;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class UpdateActivityRequest extends StoreActivityRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize("update", [
            $this->activity,
            $this->journey,
            true,
        ]);
    }
}
