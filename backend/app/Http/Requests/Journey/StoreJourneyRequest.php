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
        return Gate::authorize('create', [Journey::class, true]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'destination' => 'required|string',
            'from' => 'required|date',
            'to' => 'required|date',
            'invite' => 'sometimes|uuid',
            'mapbox_id' => 'nullable|string',
            'mapbox_full_address' => 'nullable|string',
            'template_id' => 'nullable|exists:journeys,id',
            'calendar_activity_insert_mode' => 'required_with:template_id|string|in:direct,smart,pool',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Name of the journey.',
                'example' => 'Seine-sational trip',
            ],
            'destination' => [
                'description' => 'Destination of the journey.',
                'example' => 'Paris',
            ],
            'from' => [
                'description' => 'Start date of the journey.',
                'example' => '2021-01-01',
            ],
            'to' => [
                'description' => 'End date of the journey.',
                'example' => '2021-01-10',
            ],
            'invite' => [
                'description' => 'Invite code of the journey.',
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'mapbox_id' => [
                'description' => 'Mapbox id of the journey.',
                'example' => 'mapbox.123456',
            ],
            'mapbox_full_address' => [
                'description' => 'Mapbox full address of the journey.',
                'example' => 'Paris, France',
            ],
            'template_id' => [
                'description' => 'Template id of the journey.',
                'example' => '123e4567-e89b-12d3-a456-426614174000',
            ],
            'calendar_activity_insert_mode' => [
                'description' => 'Calendar activity insert mode of the journey if generating from a template.',
                'example' => 'direct',
            ],
        ];
    }
}
