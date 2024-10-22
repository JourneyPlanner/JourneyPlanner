<?php

namespace App\Http\Requests\Journey;

use App\Http\Requests\Journey\StoreJourneyRequest;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class UpdateJourneyRequest extends StoreJourneyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize("update", [$this->journey, true]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get the rules from the parent class
        $rules = parent::rules();

        // Remove the invite rule as invites should not be updated
        unset($rules["invite"]);

        return $rules;
    }
}
