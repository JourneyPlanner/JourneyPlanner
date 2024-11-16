<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Journey;

class WeatherService
{
    /**
     * Get the weather data for the journey.
     * @param Journey $journey The journey to get weather data for.
     * @return array The weather data.
     */
    public function getWeatherData(Journey $journey): array
    {
        $response = Http::get(
            "https://api.open-meteo.com/v1/forecast?latitude={$journey->latitude}&longitude={$journey->longitude}&current=temperature_2m,weather_code&daily=weather_code,temperature_2m_max,temperature_2m_min&timezone=auto&forecast_days=4"
        );

        $weatherData = $response->json();

        if (!isset($weatherData["current"]) || !isset($weatherData["daily"])) {
            return ["error" => "Weather data not available"];
        }

        return [
            "current" => [
                "temperature" => $weatherData["current"]["temperature_2m"],
                "weather_code" => $weatherData["current"]["weather_code"],
            ],
            "forecast" => array_map(function ($index) use ($weatherData) {
                return [
                    "date" => $weatherData["daily"]["time"][$index],
                    "temperature_max" =>
                        $weatherData["daily"]["temperature_2m_max"][$index],
                    "temperature_min" =>
                        $weatherData["daily"]["temperature_2m_min"][$index],
                    "weather_code" =>
                        $weatherData["daily"]["weather_code"][$index],
                ];
            }, range(0, 3)),
        ];
    }
}
