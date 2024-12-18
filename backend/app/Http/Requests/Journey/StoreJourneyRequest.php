<?php

namespace App\Http\Requests\Journey;

use App\Models\Journey;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreJourneyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize("create", [Journey::class, true]);
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
            "destination" => "required|string",
            "from" => "required|date",
            "to" => "required|date",
            "invite" => "sometimes|uuid",
            "mapbox_id" => "nullable|string",
            "mapbox_full_address" => "nullable|string",
            "template_id" => "nullable|exists:journeys,id",
            "calendar_activity_insert_mode" =>
                "required_with:template_id|string|in:direct,smart,pool",
        ];
    }
}
