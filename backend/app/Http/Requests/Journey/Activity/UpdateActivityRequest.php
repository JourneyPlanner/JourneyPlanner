<?php

namespace App\Http\Requests\Journey\Activity;

use App\Http\Requests\Journey\Activity\StoreActivityRequest;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class UpdateActivityRequest extends StoreActivityRequest
{
    public const EDIT_TYPE_SINGLE = "single";
    public const EDIT_TYPE_FOLLOWING = "following";
    public const EDIT_TYPE_ALL = "all";

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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules["name"] = "nullable|string";
        $rules["estimated_duration"] = "nullable|date_format:H:i:s";
        $rules["edit_type"] = "required|in:single,following,all";
        $rules["calendar_activity_id"] =
            "required_unless:edit_type,all|exists:calendar_activities,id";
        return $rules;
    }
}
