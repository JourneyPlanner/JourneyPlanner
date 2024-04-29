<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JourneyRequest extends FormRequest
{
    /**
     * Get the validation rules for a journey.
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
        ];
    }
}
