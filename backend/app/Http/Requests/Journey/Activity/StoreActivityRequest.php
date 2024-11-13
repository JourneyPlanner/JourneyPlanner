<?php

namespace App\Http\Requests\Journey\Activity;

use App\Models\Activity;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize("create", [
            Activity::class,
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
        return [
            "name" => "required|string",
            "estimated_duration" => "required|date_format:H:i:s",
            "mapbox_id" => "nullable|string",
            "mapbox_full_address" => "nullable|string",
            "address" => "nullable|string",
            "opening_hours" => "nullable|string",
            "email" => "nullable|email",
            "phone" => "nullable|string",
            "link" => "nullable|url",
            "cost" => "nullable|string",
            "description" => "nullable|string",
            "date" => "nullable|date",
            "time" => "nullable|date_format:H:i",
            "repeat_type" => "nullable|in:daily,weekly,custom",
            "repeat_interval" => "required_if:repeat_type,custom|integer",
            "repeat_interval_unit" =>
                "required_if:repeat_type,custom|in:days,weeks",
            "repeat_on" => "required_if:repeat_interval_unit,weeks|array",
            "repeat_on.*" => "in:Mon,Tue,Wed,Thu,Fri,Sat,Sun",
            "repeat_end_date" => "nullable|date",
            "repeat_end_occurrences" => "nullable|integer",
        ];
    }
}
