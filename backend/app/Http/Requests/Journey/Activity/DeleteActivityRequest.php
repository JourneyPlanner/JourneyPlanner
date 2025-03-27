<?php

namespace App\Http\Requests\Journey\Activity;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DeleteActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize('delete', [
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
            'edit_type' => 'required|in:single,following,all',
            'calendar_activity_id' => 'required_unless:edit_type,all|exists:calendar_activities,id',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'edit_type' => [
                'description' => 'The type of edit to perform',
                'example' => 'single',
            ],
            'calendar_activity_id' => [
                'description' => 'The ID of the calendar activity to delete',
                'example' => '9c0d214d-04ca-4ec8-8620-c8000f49c77b',
            ],
        ];
    }
}
