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
        return Gate::authorize('create', [
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
            'name' => 'required|string',
            'estimated_duration' => 'required|date_format:H:i:s',
            'mapbox_id' => 'nullable|string',
            'mapbox_full_address' => 'nullable|string',
            'address' => 'nullable|string',
            'opening_hours' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'link' => 'nullable|url',
            'cost' => 'nullable|string',
            'description' => 'nullable|string',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
            'repeat_type' => 'nullable|in:daily,weekly,custom',
            'repeat_interval' => 'required_if:repeat_type,custom|integer',
            'repeat_interval_unit' => 'required_if:repeat_type,custom|in:days,weeks',
            'repeat_on' => 'required_if:repeat_interval_unit,weeks|nullable',
            'repeat_on.*' => 'in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            'repeat_end_date' => 'nullable|date',
            'repeat_end_occurrences' => 'nullable|integer',
        ];
    }

    public function bodyParameters()
    {
        return [
            'name' => [
                'description' => 'The name of the activity',
                'example' => 'Museum of Modern Art',
            ],
            'estimated_duration' => [
                'description' => 'The estimated duration of the activity',
                'example' => '02:00:00',
            ],
            'mapbox_id' => [
                'description' => 'The Mapbox ID of the activity',
                'example' => 'poi.1234567890',
            ],
            'mapbox_full_address' => [
                'description' => 'The full address of the activity',
                'example' => '11 W 53rd St, New York, NY 10019, USA',
            ],
            'address' => [
                'description' => 'The address of the activity',
                'example' => '11 W 53rd St',
            ],
            'opening_hours' => [
                'description' => 'The opening hours of the activity',
                'example' => '9:00 AM - 5:00 PM',
            ],
            'email' => [
                'description' => 'The email of the activity',
                'example' => 'email@example.com',
            ],
            'phone' => [
                'description' => 'The phone number of the activity',
                'example' => '+1 212-708-9400',
            ],
            'link' => [
                'description' => 'The link of the activity',
                'example' => 'https://www.moma.org/',
            ],
            'cost' => [
                'description' => 'The cost of the activity',
                'example' => '$25',
            ],
            'description' => [
                'description' => 'The description of the activity',
                'example' => 'The Museum of Modern Art is an art museum located in Midtown Manhattan, New York City, on 53rd Street between Fifth and Sixth Avenues.',
            ],
            'date' => [
                'description' => 'The start date of the activity',
                'example' => '2021-12-25',
            ],
            'time' => [
                'description' => 'The start time of the activity',
                'example' => '09:00',
            ],
            'repeat_type' => [
                'description' => 'The repeat type of the activity',
                'example' => 'daily',
            ],
            'repeat_interval' => [
                'description' => 'The repeat interval of the activity',
                'example' => 3,
            ],
            'repeat_interval_unit' => [
                'description' => 'The repeat interval unit of the activity',
                'example' => 'days',
            ],
            'repeat_on.*' => [
                'description' => 'An array of the days to repeat the activity on if the repeat interval unit is weeks.',
                'example' => ['Mon', 'Wed', 'Fri'],
            ],
            'repeat_end_date' => [
                'description' => 'The end date of the repetition of the activity',
                'example' => '2021-12-31',
            ],
            'repeat_end_occurrences' => [
                'description' => 'The number of occurrences of the repetition of the activity',
                'example' => 5,
            ],
        ];
    }
}
