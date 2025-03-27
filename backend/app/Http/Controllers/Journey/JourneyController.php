<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Journey\Activity\ActivityController;
use App\Http\Requests\Journey\StoreJourneyRequest;
use App\Http\Requests\Journey\UpdateJourneyRequest;
use App\Models\Journey;
use App\Models\JourneyUser;
use App\Services\MapboxService;
use App\Services\WeatherService;
use DateInterval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\UrlParam;

/**
 * @group Journey
 *
 * APIs for managing journeys.
 */
class JourneyController extends Controller
{
    /**
     * The Mapbox service.
     */
    protected $mapboxService;

    /**
     * The Weather service.
     */
    protected $weatherService;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        MapboxService $mapboxService,
        WeatherService $weatherService
    ) {
        $this->mapboxService = $mapboxService;
        $this->weatherService = $weatherService;
    }

    /**
     * Get journeys
     *
     * Display all journeys of the authenticated user.
     */
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/index.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/index.403.json', description: 'Unauthorized.')]
    // Add response fields to the documentation if we change it so that it isn't directly an array which doesn't get displayed anyway
    #[ResponseField('message', description: 'The error message.')]
    public function index()
    {
        Gate::authorize('viewAny', [Journey::class, false]);

        /**
         * @disregard P1013 Undefined method
         * This is needed for my PHP plugin to recognize the method
         */
        // Get all journeys of the authenticated user
        $journeys = Auth::user()
            ->journeys()
            ->where('is_template', false)
            ->get([
                'id',
                'name',
                'destination',
                'mapbox_full_address',
                'mapbox_id',
                'from',
                'to',
                'role',
            ]);

        return response()->json($journeys);
    }

    /**
     * Store journey
     *
     * Store the new journey and add the authenticated user to it.
     *
     * If the user isn't authenticated, the journey is created in guest mode.
     */
    #[ResponseFromFile(status: 201, file: 'storage/responses/journey/store.201.json', description: 'Success.')]
    #[ResponseFromFile(status: 422, file: 'storage/responses/journey/store.422.json', description: 'Validation error.')]
    #[ResponseField('message', description: 'The succes/error message.')]
    #[ResponseField('errors', description: 'The validation errors.')]
    #[ResponseField('journey', description: 'The created journey.')]
    #[ResponseField('journey.id', description: 'The ID of the created journey.')]
    #[ResponseField('journey.name', description: 'The name of the created journey.')]
    #[ResponseField('journey.destination', description: 'The destination of the created journey.')]
    #[ResponseField('journey.mapbox_full_address', description: 'The full address of the created journey.')]
    #[ResponseField('journey.mapbox_id', description: 'The Mapbox ID of the created journey. For internal use only.')]
    #[ResponseField('journey.from', description: 'The start date of the created journey.')]
    #[ResponseField('journey.to', description: 'The end date of the created journey.')]
    #[ResponseField('journey.longitude', description: 'The longitude of the created journey.')]
    #[ResponseField('journey.latitude', description: 'The latitude of the created journey.')]
    #[ResponseField('journey.created_at', description: 'The creation date of the created journey.')]
    #[ResponseField('journey.updated_at', description: 'The last update date of the created journey.')]
    #[ResponseField('journey.invite', description: 'The invite code of the created journey.')]
    #[ResponseField('journey.is_guest', description: 'Whether the created journey is in guest mode.')]
    #[ResponseField('journey.created_from', description: 'The ID of the template journey the created journey was created from.')]
    #[ResponseField('journey.creator', description: 'The creator of the created journey if it is a template, currently not used here. Would be provided with username and display name or slug and name in the case of a business.')]
    #[ResponseField('journey.length', description: 'The length of the journey in days.')]
    public function store(StoreJourneyRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $guestMode = ! Auth::check();

        // Handle destination and Mapbox address fetching
        $validated = $this->mapboxService->fetchAddressDetails($validated);

        $journey = new Journey($validated);
        $journey->share_id = Str::uuid();

        // Assign UUID and guest mode if applicable
        if (! $journey->invite) {
            $journey->invite = Str::uuid();
        }

        if ($guestMode) {
            $journey->is_guest = true;
        }

        // Geocoding the full address if it exists
        $journey = $this->mapboxService->setGeocodeData($journey, $validated);

        $journey->save();

        if (! $guestMode) {
            // Add the authenticated user to the journey with the role of 1 (journey guide)
            $journey->users()->attach(Auth::id(), [
                'role' => JourneyUser::JOURNEY_GUIDE_ROLE_ID,
            ]);
        }

        if (isset($validated['template_id'])) {
            $journey->created_from = $validated['template_id'];
            $journey->save();

            // Copy the activities from the template journey
            $templateJourney = Journey::where('id', $validated['template_id'])
                ->where('is_template', true)
                ->firstOrFail();
            // Calculate the time difference between template start and new journey start
            $timeshift = $templateJourney->from->diff($journey->from);
            // Calculate journey length in weeks, rounding up partial weeks
            $journeyLength = ceil($journey->from->diff($journey->to)->d / 7);
            $endDate = $journey->to;
            $endDate->setTime(23, 59, 59);
            if ($validated['calendar_activity_insert_mode'] === 'smart') {
                // Calculate additional days needed to align weekdays between template and new journey
                $additionalWeekDayShift =
                    $templateJourney->from->format('N') -
                    $journey->from->format('N');
                if ($additionalWeekDayShift < 0) {
                    $additionalWeekDayShift += 7;
                }
            }
            foreach (
                $templateJourney
                    ->activities()
                    ->with('calendarActivities')
                    ->get() as $activity
            ) {
                $newActivity = $activity->replicate();
                $newActivity->journey_id = $journey->id;
                $newActivity->save();

                // Copy the calendar activities
                if ($validated['calendar_activity_insert_mode'] === 'direct') {
                    foreach (
                        $activity->calendarActivities as $calendarActivity
                    ) {
                        $newDate = $calendarActivity->start->add($timeshift);
                        if ($newDate <= $endDate) {
                            $newCalendarActivity = $calendarActivity->replicate();
                            $newCalendarActivity->activity_id =
                                $newActivity->id;
                            $newCalendarActivity->start = $newDate;
                            $newCalendarActivity->save();
                        }
                    }
                } elseif (
                    $validated['calendar_activity_insert_mode'] === 'smart'
                ) {
                    foreach (
                        $activity->calendarActivities as $calendarActivity
                    ) {
                        $newDate = $calendarActivity->start
                            ->add($timeshift)
                            ->add(
                                new DateInterval(
                                    'P'.$additionalWeekDayShift.'D'
                                )
                            );
                        $newCalendarActivity = $calendarActivity->replicate();
                        $newCalendarActivity->activity_id = $newActivity->id;

                        if ($newDate > $endDate) {
                            if (
                                $journey->to->diff($newDate)->d <=
                                $additionalWeekDayShift + 1
                            ) {
                                $newDate = $newDate->sub(
                                    new DateInterval('P'.$journeyLength.'W')
                                );
                            } else {
                                continue;
                            }
                        }

                        if ($newDate > $journey->from) {
                            $newCalendarActivity->start = $newDate;
                            $newCalendarActivity->save();
                        }
                    }
                }
            }
        }

        return response()->json(
            [
                'message' => 'Journey created successfully',
                'journey' => $journey,
            ],
            201
        );
    }

    /**
     * Show journey
     *
     * Show the requested journey.
     */
    #[UrlParam('id', description: 'The UUID of the journey.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/show.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/show.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/show.404.json', description: 'Journey not found.')]
    #[ResponseField('message', description: 'The error message.')]
    #[ResponseField('id', description: 'The ID of the journey.')]
    #[ResponseField('name', description: 'The name of the journey.')]
    #[ResponseField('destination', description: 'The destination of the journey.')]
    #[ResponseField('mapbox_full_address', description: 'The full address of the journey.')]
    #[ResponseField('mapbox_id', description: 'The Mapbox ID of the journey. For internal use only.')]
    #[ResponseField('from', description: 'The start date of the journey.')]
    #[ResponseField('to', description: 'The end date of the journey.')]
    #[ResponseField('longitude', description: 'The longitude of the journey.')]
    #[ResponseField('latitude', description: 'The latitude of the journey.')]
    #[ResponseField('created_at', description: 'The creation date of the journey.')]
    #[ResponseField('updated_at', description: 'The last update date of the journey.')]
    #[ResponseField('invite', description: 'The invite code of the journey.')]
    #[ResponseField('is_guest', description: 'Whether the journey is in guest mode.')]
    #[ResponseField('created_from', description: 'The ID of the template journey the journey was created from.')]
    #[ResponseField('creator', description: 'The creator of the journey if it is a template, currently not used here. Would be provided with username and display name or slug and name in the case of a business.')]
    #[ResponseField('share_id', description: 'The share id for guests to view the journey.')]
    #[ResponseField('description', description: 'The description of the journey. Only used for templates.')]
    #[ResponseField('is_template', description: 'Whether the journey is a template.')]
    #[ResponseField('length', description: 'The length of the journey in days.')]
    #[ResponseField('average_rating', description: 'The average rating is only used for templates.')]
    #[ResponseField('total_ratings', description: 'The total amount of ratings is only used for templates.')]
    public function show(Journey $journey, Request $request)
    {
        $share_id = $request->input('share_id');

        // Check if the authenticated user is a member of the requested journey
        Gate::authorize('view', [$journey, true, $share_id]);

        if ($share_id) {
            unset($journey->invite);
        }

        if ($journey->is_guest) {
            unset($journey->share_id);
        }

        return response()->json($journey);
    }

    /**
     * Update journey
     *
     * Update the specified journey in storage.
     */
    #[UrlParam('id', description: 'The UUID of the journey.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/update.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/update.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/update.404.json', description: 'Journey not found.')]
    #[ResponseFromFile(status: 422, file: 'storage/responses/journey/update.422.json', description: 'Validation error.')]
    #[ResponseField('message', description: 'The error/success message.')]
    #[ResponseField('errors', description: 'The validation errors.')]
    #[ResponseField('journey.id', description: 'The ID of the journey.')]
    #[ResponseField('journey.name', description: 'The name of the journey.')]
    #[ResponseField('journey.destination', description: 'The destination of the journey.')]
    #[ResponseField('journey.mapbox_full_address', description: 'The full address of the journey.')]
    #[ResponseField('journey.mapbox_id', description: 'The Mapbox ID of the journey. For internal use only.')]
    #[ResponseField('journey.from', description: 'The start date of the journey.')]
    #[ResponseField('journey.to', description: 'The end date of the journey.')]
    #[ResponseField('journey.longitude', description: 'The longitude of the journey.')]
    #[ResponseField('journey.latitude', description: 'The latitude of the journey.')]
    #[ResponseField('journey.created_at', description: 'The creation date of the journey.')]
    #[ResponseField('journey.updated_at', description: 'The last update date of the journey.')]
    #[ResponseField('journey.invite', description: 'The invite code of the journey.')]
    #[ResponseField('journey.is_guest', description: 'Whether the journey is in guest mode.')]
    #[ResponseField('journey.created_from', description: 'The ID of the template journey the journey was created from.')]
    #[ResponseField('journey.creator', description: 'The creator of the journey if it is a template, currently not used here. Would be provided with username and display name or slug and name in the case of a business.')]
    #[ResponseField('journey.share_id', description: 'The share id for guests to view the journey.')]
    #[ResponseField('journey.description', description: 'The description of the journey. Only used for templates.')]
    #[ResponseField('journey.is_template', description: 'Whether the journey is a template.')]
    #[ResponseField('journey.length', description: 'The length of the journey in days.')]
    #[ResponseField('journey.average_rating', description: 'The average rating is only used for templates.')]
    #[ResponseField('journey.total_ratings', description: 'The total amount of ratings is only used for templates.')]
    public function update(UpdateJourneyRequest $request, Journey $journey)
    {
        $validated = $request->validated();

        $journey = self::updateJourney(
            $journey,
            $validated,
            $this->mapboxService
        );

        return response()->json(
            [
                'message' => 'Journey updated successfully',
                'journey' => $journey,
            ],
            200
        );
    }

    public static function updateJourney(
        Journey $journey,
        array $validated,
        MapboxService $mapboxService
    ) {
        // Handle destination and Mapbox address fetching
        $validated = $mapboxService->fetchAddressDetails(
            $validated,
            $journey->mapbox_full_address,
            $journey->destination
        );

        // Update journey data
        $oldMapboxFullAddress = $journey->mapbox_full_address;
        $journey->fill($validated);

        // Reset geocode data if the address has changed
        if (
            $oldMapboxFullAddress !==
            ($validated['mapbox_full_address'] ?? '')
        ) {
            $journey->longitude = null;
            $journey->latitude = null;
            $journey->mapbox_full_address = null;
            $journey = $mapboxService->setGeocodeData($journey, $validated);
        }

        if ($journey->isDirty('to')) {
            foreach (
                $journey
                    ->activities()
                    ->whereNotNull('repeat_type')
                    ->where('parent_id', null)
                    ->whereHas('calendarActivities')
                    ->get() as $activity
            ) {
                ActivityController::handleRepetitionCriteraUpdate(
                    $journey,
                    $activity
                );
            }
        }

        // Save journey
        $journey->save();

        return $journey;
    }

    /**
     * Delete journey
     *
     * Remove the specified journey from the database.
     */
    #[UrlParam('id', description: 'The UUID of the journey.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[Response(status: 204, description: 'Success. No content.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/destroy.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/destroy.404.json', description: 'Journey not found.')]
    public function destroy(Journey $journey)
    {
        Gate::authorize('delete', [$journey, true]);

        self::deleteJourney($journey);

        return response()->noContent();
    }

    /**
     * Delete the journey and its uploads.
     */
    public static function deleteJourney(Journey $journey)
    {
        // Delete journey uploads in folder
        $journeyFolder = storage_path('app/journey_media/'.$journey->id);
        if (file_exists($journeyFolder)) {
            $files = scandir($journeyFolder);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    unlink($journeyFolder.'/'.$file);
                }
            }
            rmdir($journeyFolder);
        }

        $journey->delete();
    }

    /**
     * Get weather
     *
     * Get the weather data for the specified journey.
     */
    #[UrlParam('id', description: 'The UUID of the journey.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/weather.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 400, file: 'storage/responses/journey/weather.400.json', description: 'Error retrieving weather data.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/weather.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/weather.404.json', description: 'Journey not found.')]
    #[ResponseField('message', description: 'The error message.')]
    #[ResponseField('error', description: 'The error when retrieving weather data.')]
    #[ResponseField('current', description: 'The current weather data.')]
    #[ResponseField('current.temperature', description: 'The current temperature.')]
    #[ResponseField('current.weather_code', description: 'The current weather code.')]
    #[ResponseField('forecast', description: 'The weather forecast data.')]
    #[ResponseField('forecast.date', description: 'The date of the forecast.')]
    #[ResponseField('forecast.temperature_max', description: 'The maximum temperature of the forecast.')]
    #[ResponseField('forecast.temperature_min', description: 'The minimum temperature of the forecast.')]
    #[ResponseField('forecast.weather_code', description: 'The weather code of the forecast.')]
    public function getWeather(Journey $journey)
    {
        Gate::authorize('view', [$journey, true, request()->input('share_id')]);

        $weatherDataResponse = $this->weatherService->getWeatherData($journey);

        if (isset($weatherDataResponse['error'])) {
            return response()->json(
                $weatherDataResponse,
                400
            );
        }

        return response()->json($weatherDataResponse);
    }

    /**
     * Regenerate invite
     *
     * Regenerate the invite code for the specified journey.
     */
    #[UrlParam('id', description: 'The UUID of the journey.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[ResponseFromFile(status: 200, file: 'storage/responses/journey/regenerateInvite.200.json', description: 'Success.')]
    #[ResponseFromFile(status: 403, file: 'storage/responses/journey/regenerateInvite.403.json', description: 'Unauthorized.')]
    #[ResponseFromFile(status: 404, file: 'storage/responses/journey/regenerateInvite.404.json', description: 'Journey not found.')]
    #[ResponseField('invite', description: 'The new invite code.')]
    public function regenerateInvite(Journey $journey)
    {
        Gate::authorize('update', [$journey, false]);

        $journey->invite = Str::uuid();
        $journey->save();

        return response()->json(
            [
                'invite' => $journey->invite,
            ],
            200
        );
    }
}
