<?php

namespace App\Http\Requests\Journey;

use App\Http\Requests\Journey\UpdateJourneyRequest;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class UpdateTemplateRequest extends UpdateJourneyRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize("update", [$this->journey, false, true]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        foreach ($rules as $key => $rule) {
            if (Str::contains($rule, "required_with")) {
            } elseif (Str::contains($rule, "required")) {
                $rules[$key] = str_replace(
                    "required",
                    "required_without:journey_id",
                    $rule
                );
            } else {
                $rules[$key] = $rule . "|required_without:journey_id";
            }
        }
        $rules["journey_id"] = "nullable|uuid|exists:journeys,id";
        $rules["description"] = "nullable|string";
        return $rules;
    }
}
