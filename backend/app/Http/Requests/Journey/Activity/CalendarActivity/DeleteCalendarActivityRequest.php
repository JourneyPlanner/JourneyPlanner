<?php

namespace App\Http\Requests\Journey\Activity\CalendarActivity;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DeleteCalendarActivityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): Response
    {
        return Gate::authorize('delete', [
            $this->calendarActivity,
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
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'edit_type' => [
                'description' => 'The type of delete to perform. single: only this activity, following: this and all following activities, all: all activities',
                'example' => 'single',
            ],
        ];
    }
}
