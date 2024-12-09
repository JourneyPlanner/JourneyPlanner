<?php

namespace App\Http\Requests\Journey\Activity;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Access\Response;

class DeleteActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize("delete", [
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
        return [
            "edit_type" => "required|in:single,following,all",
            "calendar_activity_id" =>
                "required_unless:edit_type,all|exists:calendar_activities,id",
        ];
    }
}
