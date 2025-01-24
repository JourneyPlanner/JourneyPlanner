<?php

namespace App\Http\Requests\Journey;

use App\Http\Requests\Journey\UpdateJourneyRequest;

class UpdateTemplateRequest extends UpdateJourneyRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules["journey_id"] = "nullable|uuid|exists:journeys,id";
        return $rules;
    }
}
