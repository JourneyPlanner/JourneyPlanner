<?php

namespace App\Http\Controllers\Journey\Activity;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journey\Activity\DeleteActivityRequest;
use App\Http\Requests\Journey\Activity\StoreActivityRequest;
use App\Http\Requests\Journey\Activity\UpdateActivityRequest;
use App\Models\Activity;
use App\Models\CalendarActivity;
use App\Models\Journey;
use App\Services\MapboxService;
use DateInterval;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromFile;
use Knuckles\Scribe\Attributes\UrlParam;

/**
 * @group Activity
 * APIs for working with journey activities.
 */
class ActivityController extends Controller
{
    /**
     * The Mapbox service.
     */
    protected $mapboxService;

    /**
     * Create a new controller instance.
     */
    public function __construct(MapboxService $mapboxService)
    {
        $this->mapboxService = $mapboxService;
    }

    /**
     * Get activities
     *
     * Get all activities of the specified journey.
     */
    #[UrlParam('journey_id', description: 'The UUID of the journey.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[ResponseFromFile('storage/responses/journey/activity/index.200.json', status: 200, description: 'Success.')]
    #[ResponseFromFile('storage/responses/journey/activity/index.404.json', status: 404, description: 'Journey not found.')]
    #[ResponseFromFile('storage/responses/journey/activity/index.403.json', status: 403, description: 'Unauthorized.')]
    #[ResponseField('message', description: 'The error message.')]
    #[ResponseField('activities', description: 'The activities of the journey.')]
    #[ResponseField('activities.id', description: 'The ID of the activity.')]
    #[ResponseField('activities.journey_id', description: 'The ID of the journey that the activity belongs to.')]
    #[ResponseField('activities.name', description: 'The name of the activity.')]
    #[ResponseField('activities.estimated_duration', description: 'The estimated duration of the activity.')]
    #[ResponseField('activities.opening_hours', description: 'The opening hours of the activity.')]
    #[ResponseField('activities.email', description: 'The email of the activity.')]
    #[ResponseField('activities.phone', description: 'The phone number of the activity.')]
    #[ResponseField('activities.link', description: 'The link of the activity.')]
    #[ResponseField('activities.cost', description: 'The cost of the activity.')]
    #[ResponseField('activities.description', description: 'The description of the activity.')]
    #[ResponseField('activities.mapbox_id', description: 'The Mapbox ID of the activity, only for internal use.')]
    #[ResponseField('activities.mapbox_full_address', description: 'The full address of the activity as provided by Mapbox.')]
    #[ResponseField('activities.address', description: 'The address of the activity as provided by the user.')]
    #[ResponseField('activities.longitude', description: 'The longitude of the activity provided by Mapbox.')]
    #[ResponseField('activities.latitude', description: 'The latitude of the activity provided by Mapbox.')]
    #[ResponseField('activities.created_at', description: 'The creation date of the activity.')]
    #[ResponseField('activities.updated_at', description: 'The last update date of the activity.')]
    #[ResponseField('activities.repeat_type', description: 'The type of repetition of the activity.', enum: ['daily', 'weekly', 'custom'])]
    #[ResponseField('activities.repeat_interval', description: 'The repetition interval of the activity.')]
    #[ResponseField('activities.repeat_interval_unit', description: 'The unit of the interval of repetition of the activity.', enum: ['days', 'weeks'])]
    #[ResponseField('activities.repeat_on', description: 'The days of the week on which the activity is repeated. Array of day short forms.', enum: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])]
    #[ResponseField('activities.repeat_end_date', description: 'The end date of the repetition of the activity.')]
    #[ResponseField('activities.repeat_end_occurrences', description: 'The number of occurrences of the repetition of the activity.')]
    #[ResponseField('activities.parent_id', description: 'The ID of the parent activity if the activity is a sub-activity.')]
    #[ResponseField('activities.calendar_activities', description: 'The calendar activities of the activity.')]
    #[ResponseField('activities.calendar_activities.id', description: 'The ID of the calendar activity.')]
    #[ResponseField('activities.calendar_activities.activity_id', description: 'The ID of the activity that the calendar activity belongs to.')]
    #[ResponseField('activities.calendar_activities.start', description: 'The start date of the calendar activity.')]
    #[ResponseField('activities.calendar_activities.created_at', description: 'The creation date of the calendar activity.')]
    #[ResponseField('activities.calendar_activities.updated_at', description: 'The last update date of the calendar activity.')]
    #[ResponseField('count', description: 'The number of activities.')]
    public function index(Journey $journey, Request $request): JsonResponse
    {
        Gate::authorize('viewAny', [
            Activity::class,
            $journey,
            true,
            request()->input('share_id'),
        ]);

        $activities = $journey->activities()->with('calendarActivities')->get();

        return response()->json([
            'activities' => $activities,
            'count' => $activities->count(),
        ]);
    }

    /**
     * Store activity
     *
     * Store a newly created activity in storage.
     */
    #[UrlParam('journey_id', description: 'The UUID of the journey.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[ResponseFromFile('storage/responses/journey/activity/store.201.json', status: 201, description: 'Success. Returns the activites with all details that have been stored and includes calendar activities if applicable. For a description of the parameters (and to see all possible options), see the request body.')]
    #[ResponseFromFile('storage/responses/journey/activity/store.403.json', status: 403, description: 'Unauthorized.')]
    #[ResponseFromFile('storage/responses/journey/activity/store.404.json', status: 404, description: 'Journey not found.')]
    #[ResponseFromFile('storage/responses/journey/activity/store.422.json', status: 422, description: 'Validation error.')]
    #[ResponseField('message', description: 'The error message.')]
    #[ResponseField('errors', description: 'The validation errors, may include every field.')]
    public function store(
        StoreActivityRequest $request,
        Journey $journey
    ): JsonResponse {
        // Validate the request
        $validated = $request->validated();
        // Limit the number of activities for guests
        if ($journey->is_guest && $journey->activities()->count() >= 10) {
            abort(403, 'You have reached the maximum number of activities.');
        }

        // Handle destination and Mapbox address fetching
        $validated = $this->mapboxService->fetchAddressDetails($validated);

        // Create the activity
        $activity = new Activity($validated);
        $activity->journey_id = $journey->id;

        // Get the longitude and latitude of the address if it exists
        $activity = $this->mapboxService->setGeocodeData($activity, $validated);

        $activity->save();

        // Create the calendar activity if the date is provided
        if (! static::createCalendarActivityIfNeeded($validated, $activity)) {
            $activity['calendar_activities'] = [];

            return response()->json($activity, 201);
        }

        // Handle repeating activities
        if (! isset($validated['repeat_type'])) {
            return response()->json($activity->load('calendarActivities'), 201);
        }

        $calendarActivity = $activity->calendarActivities()->first();
        if (! $calendarActivity) {
            return response()->json($activity, 201);
        }

        static::handleRepeatingActivity($journey, $activity, $calendarActivity);

        return response()->json($activity->load('calendarActivities'), 201);
    }

    /**
     * Show activity
     *
     * Display the specified activity including its calendar activities.
     */
    #[UrlParam('journey_id', description: 'The UUID of the journey.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[UrlParam('id', description: 'The UUID of the activity.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[ResponseFromFile('storage/responses/journey/activity/show.200.json', status: 200, description: 'Success.')]
    #[ResponseFromFile('storage/responses/journey/activity/show.403.json', status: 403, description: 'Unauthorized.')]
    #[ResponseFromFile('storage/responses/journey/activity/show.404.json', status: 404, description: 'Journey or activity not found.')]
    #[ResponseField('message', description: 'The error message.')]
    #[ResponseField('id', description: 'The ID of the activity.')]
    #[ResponseField('journey_id', description: 'The ID of the journey that the activity belongs to.')]
    #[ResponseField('name', description: 'The name of the activity.')]
    #[ResponseField('estimated_duration', description: 'The estimated duration of the activity.')]
    #[ResponseField('opening_hours', description: 'The opening hours of the activity.')]
    #[ResponseField('email', description: 'The email of the activity.')]
    #[ResponseField('phone', description: 'The phone number of the activity.')]
    #[ResponseField('link', description: 'The link of the activity.')]
    #[ResponseField('cost', description: 'The cost of the activity.')]
    #[ResponseField('description', description: 'The description of the activity.')]
    #[ResponseField('mapbox_id', description: 'The Mapbox ID of the activity, only for internal use.')]
    #[ResponseField('mapbox_full_address', description: 'The full address of the activity as provided by Mapbox.')]
    #[ResponseField('address', description: 'The address of the activity as provided by the user.')]
    #[ResponseField('longitude', description: 'The longitude of the activity provided by Mapbox.')]
    #[ResponseField('latitude', description: 'The latitude of the activity provided by Mapbox.')]
    #[ResponseField('created_at', description: 'The creation date of the activity.')]
    #[ResponseField('updated_at', description: 'The last update date of the activity.')]
    #[ResponseField('repeat_type', description: 'The type of repetition of the activity.', enum: ['daily', 'weekly', 'custom'])]
    #[ResponseField('repeat_interval', description: 'The repetition interval of the activity.')]
    #[ResponseField('repeat_interval_unit', description: 'The unit of the interval of repetition of the activity.', enum: ['days', 'weeks'])]
    #[ResponseField('repeat_on', description: 'The days of the week on which the activity is repeated. Array of day short forms.', enum: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'])]
    #[ResponseField('repeat_end_date', description: 'The end date of the repetition of the activity.')]
    #[ResponseField('repeat_end_occurrences', description: 'The number of occurrences of the repetition of the activity.')]
    #[ResponseField('parent_id', description: 'The ID of the parent activity if the activity is a sub-activity.')]
    #[ResponseField('calendar_activities', description: 'The calendar activities of the activity.')]
    #[ResponseField('calendar_activities.id', description: 'The ID of the calendar activity.')]
    #[ResponseField('calendar_activities.activity_id', description: 'The ID of the activity that the calendar activity belongs to.')]
    #[ResponseField('calendar_activities.start', description: 'The start date of the calendar activity.')]
    #[ResponseField('calendar_activities.created_at', description: 'The creation date of the calendar activity.')]
    #[ResponseField('calendar_activities.updated_at', description: 'The last update date of the calendar activity.')]
    public function show(Journey $journey, Activity $activity)
    {
        Gate::authorize('view', [
            $activity,
            $journey,
            true,
            request()->input('share_id'),
        ]);

        return response()->json($activity->load('calendarActivities'));
    }

    /**
     * Update activity
     *
     * Update the specified activity in storage.
     */
    #[UrlParam('journey_id', description: 'The UUID of the journey.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[UrlParam('id', description: 'The UUID of the activity.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[ResponseFromFile('storage/responses/journey/activity/update.200.json', status: 200, description: 'Success. Returns the activites with all details that have been updated and includes calendar activities if applicable. For a description of the parameters (and to see all possible options), see the request body.')]
    #[ResponseFromFile('storage/responses/journey/activity/update.403.json', status: 403, description: 'Unauthorized.')]
    #[ResponseFromFile('storage/responses/journey/activity/update.404.json', status: 404, description: 'Journey or activity not found.')]
    #[ResponseFromFile('storage/responses/journey/activity/update.422.json', status: 422, description: 'Validation error.')]
    #[ResponseField('message', description: 'The error message.')]
    #[ResponseField('errors', description: 'The validation errors, may include every field.')]
    public function update(
        UpdateActivityRequest $request,
        Journey $journey, // Do not remove the journey parameter, it is required for authorization.
        Activity $activity
    ) {
        // Validate the request
        $validated = $request->validated();
        $generalizeBaseActivityInsteadOfDeleting = false;

        $validated = $this->mapboxService->fetchAddressDetails(
            $validated,
            $activity->mapbox_full_address,
            $activity->address
        );

        $repeatedChanged =
            array_key_exists('repeat_type', $validated) &&
            (($activity->repeat_type ?? '') != $validated['repeat_type'] ||
                ($activity->repeat_interval ?? 0) !=
                ($validated['repeat_interval'] ?? 0) ||
                ($activity->repeat_interval_unit ?? '') !=
                ($validated['repeat_interval_unit'] ?? '') ||
                ($activity->repeat_on ?? ['']) !=
                ($validated['repeat_on'] ?? ['']) ||
                ($activity->repeat_end_date ?? '') !=
                ($validated['repeat_end_date'] ?? '') ||
                ($activity->repeat_end_occurrences ?? 0) !=
                ($validated['repeat_end_occurrences'] ?? 0));

        $timeDifference = null;
        // Create the calendar activity if the date is provided and the activity is not repeated and has no calendar activities
        if ($activity->calendarActivities()->count() === 0) {
            $calendarActivityCreated = static::createCalendarActivityIfNeeded(
                $validated,
                $activity
            );

            if ($calendarActivityCreated && $activity->repeat_type != null) {
                $calendarActivity = $activity->calendarActivities()->first();

                if ($calendarActivity) {
                    static::handleRepeatingActivity(
                        $journey,
                        $activity,
                        $calendarActivity
                    );
                }
            }
        } elseif (isset($validated['date'])) {
            $editedCalendarActivity = CalendarActivity::findOrFail(
                $validated['calendar_activity_id']
            );

            Gate::authorize('update', [
                $editedCalendarActivity,
                $activity,
                $journey,
                true,
            ]);

            if (! isset($validated['time'])) {
                $validated['time'] = '00:00:00';
            }

            $newStart = new DateTime(
                $validated['date'].' '.$validated['time']
            );

            $timeDifference = $editedCalendarActivity->start->diff($newStart);
            // Set time difference to null if the difference is 0
            if (
                $timeDifference->y == 0 &&
                $timeDifference->m == 0 &&
                $timeDifference->d == 0 &&
                $timeDifference->h == 0 &&
                $timeDifference->i == 0 &&
                $timeDifference->s == 0
            ) {
                $timeDifference = null;
            }
        }

        // Edit repeated activities
        $baseActivity = $activity->getBaseActivity();
        $activities = [];
        if ($validated['edit_type'] == UpdateActivityRequest::EDIT_TYPE_ALL) {
            $repeatTypeChanged =
                array_key_exists('repeat_type', $validated) &&
                ($activity->repeat_type ?? '') != $validated['repeat_type'];
            if ($repeatTypeChanged) {
                $activity = static::resetRepeat($activity);
            }
            $activity->fill($validated);

            // Reset geocode data if the address has changed
            static::resetGeocodeDataIfNeeded(
                $activity,
                $validated,
                $this->mapboxService
            );
            $activity->save();
            static::addTimeDifferenceIfNeeded($activity, $timeDifference);

            // Get the changes to apply to the activities
            $changes = $activity->getChanges();

            // Update the remaining activities
            if ($baseActivity->id !== $activity->id) {
                if ($repeatTypeChanged) {
                    $activity = static::resetRepeat($activity);
                }
                $baseActivity->update($changes);
                static::addTimeDifferenceIfNeeded(
                    $baseActivity,
                    $timeDifference
                );
            }
            foreach ($baseActivity->children()->get() as $child) {
                if ($child->id === $activity->id) {
                    continue;
                }
                if ($repeatTypeChanged) {
                    $activity = static::resetRepeat($activity);
                }
                $child->update($changes);
                static::addTimeDifferenceIfNeeded($child, $timeDifference);
            }

            // Handle repeating activities
            if ($repeatedChanged) {
                if ($activity->repeat_type == null) {
                    $editedCalendarActivity = CalendarActivity::findOrFail(
                        $validated['calendar_activity_id']
                    );

                    Gate::authorize('update', [
                        $editedCalendarActivity,
                        $activity,
                        $journey,
                        true,
                    ]);

                    array_push(
                        $activities,
                        ...static::deleteAllCalendarActivitiesExcept(
                            $editedCalendarActivity,
                            $baseActivity
                        )
                    );
                    $generalizeBaseActivityInsteadOfDeleting = true;
                } else {
                    static::handleRepetitionCriteraUpdate(
                        $journey,
                        $baseActivity
                    );
                }
            }
        } else {
            $editedCalendarActivity = CalendarActivity::findOrFail(
                $validated['calendar_activity_id']
            );

            Gate::authorize('update', [
                $editedCalendarActivity,
                $activity,
                $journey,
                true,
            ]);

            if (
                $validated['edit_type'] ==
                UpdateActivityRequest::EDIT_TYPE_SINGLE
            ) {
                if ($repeatedChanged && $validated['repeat_type'] != null) {
                    abort(
                        400,
                        'You cannot change the repeat settings of a single activity.'
                    );
                }
                $subActivity = $activity->replicate();
                $subActivity->fill($validated);
                static::resetGeocodeDataIfNeeded(
                    $activity,
                    $validated,
                    $this->mapboxService
                );
                if ($repeatedChanged) {
                    $subActivity = static::resetRepeat($subActivity);
                    $subActivity->parent_id = null;
                } else {
                    $subActivity->parent_id = $baseActivity->id;
                }
                $subActivity->save();

                $editedCalendarActivity->activity_id = $subActivity->id;
                $editedCalendarActivity->save();

                if ($timeDifference) {
                    $editedCalendarActivity->start = $editedCalendarActivity->start->add(
                        $timeDifference
                    );
                    $editedCalendarActivity->save();
                }

                if ($repeatedChanged) {
                    $activities[] = $subActivity->load('calendarActivities');
                }
            } else {
                // Update the current activity, get changes and then apply changes to all activities after this one
                $editedActivity = static::updateActivitiesAfter(
                    $activity,
                    $baseActivity,
                    $editedCalendarActivity->start,
                    $timeDifference,
                    $validated,
                    true,
                    $this->mapboxService
                );

                if ($repeatedChanged) {
                    foreach (
                        $baseActivity->children()->get() as $childActivity
                    ) {
                        if ($childActivity->id === $editedActivity->id) {
                            continue;
                        }
                        $activities[] = static::updateActivitiesAfter(
                            $childActivity,
                            $baseActivity,
                            $editedCalendarActivity->start,
                            $timeDifference,
                            $editedActivity->getChanges(),
                            false,
                            $this->mapboxService
                        )->load('calendarActivities');
                    }

                    $mappings = static::calculateDateToActivityMappings(
                        $editedActivity
                    );

                    if ($editedActivity->repeat_type != null) {
                        static::deleteAllCalendarActivitiesAfterBaseActivity(
                            $baseActivity,
                            $editedCalendarActivity->start,
                            false,
                            false
                        );

                        static::handleRepeatingActivity(
                            $journey,
                            $editedActivity,
                            $editedCalendarActivity,
                            $mappings
                        );
                    } else {
                        array_push(
                            $activities,
                            ...static::deleteAllCalendarActivitiesAfterBaseActivity(
                                $baseActivity,
                                $editedCalendarActivity->start,
                                false,
                                false
                            )
                        );

                        $generalizeBaseActivityInsteadOfDeleting = true;
                    }

                    $editedActivity->parent_id = null;
                    $editedActivity->save();

                    $activities[] = $editedActivity->load('calendarActivities');
                } else {
                    foreach (
                        $baseActivity->children()->get() as $childActivity
                    ) {
                        if ($childActivity->id === $editedActivity->id) {
                            continue;
                        }
                        static::updateActivitiesAfter(
                            $childActivity,
                            $baseActivity,
                            $editedCalendarActivity->start,
                            $timeDifference,
                            $editedActivity->getChanges(),
                            false,
                            $this->mapboxService
                        );
                    }
                }
            }
        }

        // Delete activities without calendar activities unless they are the base activity
        static::deleteActivitiesWithoutCalendarActivities($baseActivity);

        // Compare all activities with each other, if they have the same data, merge them
        static::mergeDuplicateActivities($baseActivity);

        // Replace base activity if it doesn't have any calendar activities
        if (
            ! $baseActivity->calendarActivities()->count() &&
            $baseActivity->children()->count()
        ) {
            $replacementActivity = static::replaceBaseActivity($baseActivity);

            if ($generalizeBaseActivityInsteadOfDeleting) {
                $activities[] = $baseActivity;
            } else {
                $baseActivity->delete();
            }
            $baseActivity = $replacementActivity;
        }

        // Combine newly created activities with the base activity and its children
        array_push(
            $activities,
            ...$baseActivity->children()->with('calendarActivities')->get()
        );
        $activities[] = $baseActivity->load('calendarActivities');

        return response()->json($activities, 200);
    }

    /**
     * Delete activity
     *
     * Delete the specified activity from storage.
     */
    #[UrlParam('journey_id', description: 'The UUID of the journey.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[UrlParam('id', description: 'The UUID of the activity.', type: 'uuid', example: '9c23279f-f2a7-4666-bc00-56dfe0c47342')]
    #[ResponseFromFile('storage/responses/journey/activity/destroy.200.json', status: 200, description: 'Success. Returns the activites with all details that have been deleted and includes calendar activities if applicable.')]
    #[ResponseFromFile('storage/responses/journey/activity/destroy.403.json', status: 403, description: 'Unauthorized.')]
    #[ResponseFromFile('storage/responses/journey/activity/destroy.404.json', status: 404, description: 'Journey or activity not found.')]
    #[ResponseFromFile('storage/responses/journey/activity/destroy.422.json', status: 422, description: 'Validation error.')]
    #[ResponseField('message', description: 'The error message.')]
    #[ResponseField('errors', description: 'The validation errors, may include every field.')]
    public function destroy(
        DeleteActivityRequest $request,
        Journey $journey,
        Activity $activity
    ) {
        Gate::authorize('delete', [$activity, $journey, true]);
        $validated = $request->validated();
        $baseActivity = $activity->getBaseActivity();

        if ($validated['edit_type'] == UpdateActivityRequest::EDIT_TYPE_ALL) {
            $baseActivity->children()->delete();
            $baseActivity->delete();

            return response()->json([], 200);
        }

        $calendarActivity = CalendarActivity::findOrFail(
            $validated['calendar_activity_id']
        );

        if (
            $validated['edit_type'] == UpdateActivityRequest::EDIT_TYPE_SINGLE
        ) {
            $calendarActivity->delete();
            static::deleteActivityIfNeeded($activity);
        } elseif (
            $validated['edit_type'] ==
            UpdateActivityRequest::EDIT_TYPE_FOLLOWING
        ) {
            static::deleteAllCalendarActivitiesAfterBaseActivity(
                $activity,
                $calendarActivity->start,
                true,
                true
            );
        }

        $activities = $baseActivity
            ->children()
            ->with('calendarActivities')
            ->get();
        $activities[] = $baseActivity->load('calendarActivities');

        return response()->json($activities, 200);
    }

    /**
     * Reset all repeat options of an activity.
     */
    public static function resetRepeat(Activity $activity)
    {
        $activity->fill([
            'repeat_type' => null,
            'repeat_interval' => null,
            'repeat_interval_unit' => null,
            'repeat_on' => null,
            'repeat_end_date' => null,
            'repeat_end_occurrences' => null,
        ]);

        return $activity;
    }

    /**
     * Delete all calendar activities except the specified one.
     */
    private static function deleteAllCalendarActivitiesExcept(
        CalendarActivity $calendarActivityToKeep,
        Activity $baseActivity
    ): array {
        $activities = [];
        foreach (
            $baseActivity->children()->with('calendarActivities')->get() as $child
        ) {
            foreach ($child->calendarActivities as $calendarActivity) {
                if ($calendarActivity->id != $calendarActivityToKeep->id) {
                    $calendarActivity->delete();
                }
            }
            if ($child->calendarActivities()->count() === 0) {
                $child->parent_id = null;
                $child->save();
                $activities[] = $child;
            }
        }

        foreach (
            $baseActivity->calendarActivities()->get() as $calendarActivity
        ) {
            if ($calendarActivity->id != $calendarActivityToKeep->id) {
                $calendarActivity->delete();
            }
        }

        return $activities;
    }

    public static function handleRepetitionCriteraUpdate(
        Journey $journey,
        Activity $baseActivity
    ) {
        $mappings = static::calculateDateToActivityMappings($baseActivity);
        // Get the first calendar activity which is
        $firstCalendarActivity = $baseActivity
            ->calendarActivities()
            ->orderBy('start')
            ->first();

        foreach ($baseActivity->children()->get() as $child) {
            $childFirstCalendarActivity = $child
                ->calendarActivities()
                ->orderBy('start')
                ->first();
            if (
                $childFirstCalendarActivity &&
                $childFirstCalendarActivity->start <
                $firstCalendarActivity->start
            ) {
                $firstCalendarActivity = $childFirstCalendarActivity;
            }
        }
        static::deleteAllCalendarActivitiesAfterBaseActivity(
            $baseActivity,
            $firstCalendarActivity->start,
            false,
            false
        );
        static::handleRepeatingActivity(
            $journey,
            $baseActivity,
            $firstCalendarActivity,
            $mappings
        );
    }

    /**
     * Add a time difference to the calendar activities of the activity.
     */
    private static function addTimeDifferenceIfNeeded(
        Activity $activity,
        ?DateInterval $timeDifference
    ) {
        if ($timeDifference) {
            foreach (
                $activity->calendarActivities()->get() as $calendarActivity
            ) {
                $calendarActivity->start = $calendarActivity->start->add(
                    $timeDifference
                );
                $calendarActivity->save();
            }
        }
    }

    /**
     * Calculate the date ranges for the sub activities of the base activity.
     */
    private static function calculateDateToActivityMappings(
        Activity $baseActivity
    ) {
        // Create a mapping from date to activity
        $dateToActivityMappings = [];
        foreach (
            $baseActivity->calendarActivities()->get() as $calendarActivity
        ) {
            $dateToActivityMappings[$calendarActivity->start->format('Y-m-d')] = $baseActivity;
        }
        foreach ($baseActivity->children()->get() as $childActivity) {
            foreach (
                $childActivity->calendarActivities()->get() as $calendarActivity
            ) {
                $dateToActivityMappings[$calendarActivity->start->format('Y-m-d')] = $childActivity;
            }
        }

        // Sort the date to activity mappings by date
        ksort($dateToActivityMappings);

        return $dateToActivityMappings;
    }

    /**
     * Delete all calendar activities from an activity which start after a specified date.
     */
    private static function deleteAllCalendarActivitiesAfterBaseActivity(
        Activity $baseActivity,
        DateTime $minDate,
        bool $deleteActivities = false,
        bool $includeCurrent = true
    ): array {
        $activities = [];
        foreach ($baseActivity->children()->get() as $child) {
            static::deleteAllCalendarActivitiesAfter(
                $child,
                $minDate,
                $deleteActivities,
                $includeCurrent
            );

            if ($child->calendarActivities()->count() === 0) {
                $child->parent_id = null;
                $child->save();
                $activities[] = $child;
            }
        }

        static::deleteAllCalendarActivitiesAfter(
            $baseActivity,
            $minDate,
            $deleteActivities,
            $includeCurrent
        );

        return $activities;
    }

    /**
     * Delete all calendar activities from an activity which start after a specified date.
     */
    private static function deleteAllCalendarActivitiesAfter(
        Activity $activity,
        DateTime $minDate,
        bool $deleteActivities = false,
        bool $includeCurrent = true
    ) {
        foreach ($activity->calendarActivities()->get() as $calendarActivity) {
            if ($includeCurrent && $calendarActivity->start >= $minDate) {
                $calendarActivity->delete();
            } elseif ($calendarActivity->start > $minDate) {
                $calendarActivity->delete();
            }
        }

        if ($deleteActivities) {
            static::deleteActivityIfNeeded($activity);
        }
    }

    /**
     * Delete the activity if it has no calendar activities.
     */
    private static function deleteActivityIfNeeded(Activity $activity)
    {
        return DB::transaction(function () use ($activity) {
            if ($activity->calendarActivities()->count() === 0) {
                $activity->delete();
            }
        });
    }

    /**
     * Create a calendar activity if the date is provided.
     */
    private static function createCalendarActivityIfNeeded(
        array $validated,
        Activity $activity
    ): bool {
        if (! isset($validated['date'])) {
            return false;
        }

        if (! isset($validated['time'])) {
            $validated['time'] = '00:00:00';
        }

        $start = new DateTime($validated['date'].' '.$validated['time']);

        $calendarActivity = new CalendarActivity([
            'activity_id' => $activity->id,
            'start' => $start,
        ]);
        $calendarActivity->save();

        return true;
    }

    /**
     * Repeat the activity if needed.
     */
    private static function handleRepeatingActivity(
        Journey $journey,
        Activity $activity,
        CalendarActivity $calendarActivity,
        array $mappings = []
    ) {
        $mappingsExist = ! empty($mappings);
        $calendarActivityStart = $calendarActivity->start;
        $repeatEndDate = $activity->repeat_end_date ?? $journey->to;
        if ($repeatEndDate > $journey->to) {
            $repeatEndDate = $journey->to;
        }
        $repeatEndDate->setTime(23, 59, 59);

        if (
            $activity->repeat_type == 'custom' &&
            $activity->repeat_interval_unit == 'weeks'
        ) {
            $repeatOn = $activity->repeat_on ?? [];
            $occurences =
                $activity->repeat_end_occurrences ??
                (($calendarActivityStart->diffAsDateInterval($repeatEndDate)
                    ->days +
                    1) /
                    7) *
                count($repeatOn);
            $shiftInterval = new DateInterval('P1D');
            while ($occurences > 1) {
                $calendarActivityStart->add($shiftInterval);
                if ($calendarActivityStart > $repeatEndDate) {
                    break;
                }
                if (in_array($calendarActivityStart->format('D'), $repeatOn)) {
                    $repeatedActivity = $calendarActivity
                        ->replicate(['start'])
                        ->fill([
                            'start' => $calendarActivityStart,
                        ]);
                    if ($mappingsExist) {
                        $repeatedActivity->activity_id = static::getActivityFromDateMapping(
                            $mappings,
                            $calendarActivityStart
                        )->id;
                    }
                    $repeatedActivity->save();
                    $occurences--;
                }
                if ($calendarActivityStart->format('D') == 'Sun') {
                    $calendarActivityStart->add(
                        new DateInterval(
                            'P'.($activity->repeat_interval - 1).'W'
                        )
                    );
                }
            }
        } else {
            $repeatEveryDays =
                $activity->repeat_interval ??
                ($activity->repeat_type == 'weekly' ? 7 : 1);
            $occurences =
                $activity->repeat_end_occurrences ??
                ($calendarActivityStart->diffAsDateInterval($repeatEndDate)
                    ->days +
                    1) /
                $repeatEveryDays;
            $shiftInterval = new DateInterval('P'.$repeatEveryDays.'D');

            for ($i = 1; $i < $occurences; $i++) {
                $calendarActivityStart->add($shiftInterval);
                if ($calendarActivityStart > $repeatEndDate) {
                    break;
                }
                $repeatedActivity = $calendarActivity
                    ->replicate(['start'])
                    ->fill([
                        'start' => $calendarActivityStart,
                    ]);
                if ($mappingsExist) {
                    $repeatedActivity->activity_id = static::getActivityFromDateMapping(
                        $mappings,
                        $calendarActivityStart
                    )->id;
                }
                $repeatedActivity->save();
            }
        }
    }

    /**
     * Get the last activity before the calendar activity
     */
    private static function getActivityFromDateMapping(
        array $mappings,
        DateTime $searchDate
    ) {
        $lastActivity = null;
        foreach ($mappings as $date => $activity) {
            if ($date > $searchDate->format('Y-m-d')) {
                break;
            }
            $lastActivity = $activity;
        }

        return $lastActivity;
    }

    /**
     * Replace the current base activity with the activity that has the most calendar activities.
     */
    private static function replaceBaseActivity($baseActivity)
    {
        $replacementActivity = $baseActivity->children()->first();
        $mostCalendarActivities = $replacementActivity
            ->calendarActivities()
            ->count();
        foreach ($baseActivity->children()->get() as $childActivity) {
            if (
                $childActivity->calendarActivities()->count() >
                $mostCalendarActivities
            ) {
                $replacementActivity = $childActivity;
                $mostCalendarActivities = $childActivity
                    ->calendarActivities()
                    ->count();
            }
        }

        foreach ($baseActivity->children()->get() as $childActivity) {
            $childActivity->parent_id = $replacementActivity->id;
            $childActivity->save();
        }

        $replacementActivity->parent_id = null;
        $replacementActivity->save();

        return $replacementActivity;
    }

    /**
     * Merge duplicate activities.
     */
    private static function mergeDuplicateActivities($baseActivity)
    {
        foreach ($baseActivity->children()->get() as $childActivity) {
            if ($childActivity->hasSameAttributesAs($baseActivity)) {
                $childActivity
                    ->calendarActivities()
                    ->update(['activity_id' => $baseActivity->id]);
                $childActivity->delete();
            }
        }

        $children = $baseActivity->children()->get();
        $processed = [];
        foreach ($children as $childActivity) {
            if (in_array($childActivity->id, $processed)) {
                continue;
            }

            foreach ($children as $siblingActivity) {
                if (
                    $childActivity->id != $siblingActivity->id &&
                    $childActivity->hasSameAttributesAs($siblingActivity)
                ) {
                    // Update sibling's calendar activities to reference the current child
                    $siblingActivity
                        ->calendarActivities()
                        ->update(['activity_id' => $childActivity->id]);

                    // Mark sibling as processed and delete it
                    $processed[] = $siblingActivity->id;
                    $siblingActivity->delete();
                }
            }

            $processed[] = $childActivity->id;
        }
    }

    /**
     * Delete activities without calendar activities unless they are the base activity.
     */
    private static function deleteActivitiesWithoutCalendarActivities(
        $baseActivity
    ) {
        $baseActivity
            ->children()
            ->whereDoesntHave('calendarActivities')
            ->where('id', '!=', $baseActivity->id)
            ->delete();
    }

    /**
     * Reset the geocode data if the address has changed.
     */
    private static function resetGeocodeDataIfNeeded(
        Activity $activity,
        array $validated,
        MapboxService $mapboxService
    ) {
        if ($activity->isDirty('mapbox_full_address')) {
            $activity->longitude = null;
            $activity->latitude = null;
            $activity->mapbox_full_address = null;
            $activity = $mapboxService->setGeocodeData($activity, $validated);
        }
    }

    /**
     * Update the activities after the date of the edited calendar activity.
     */
    private static function updateActivitiesAfter(
        Activity $activity,
        Activity $baseActivity,
        DateTime $editedCalendarActivityStart,
        ?DateInterval $timeDifference,
        array $changes,
        bool $reencode,
        MapboxService $mapboxService
    ) {
        $calendarActivities = $activity->calendarActivities()->get();
        $subActivity = $activity->replicate();
        $subActivity->save();
        $repeatTypeChanged =
            array_key_exists('repeat_type', $changes) &&
            ($activity->repeat_type ?? '') != $changes['repeat_type'];
        if ($repeatTypeChanged) {
            $subActivity = static::resetRepeat($subActivity);
        }
        $subActivity->fill($changes);
        $subActivity->parent_id = $baseActivity->id;

        if ($reencode) {
            static::resetGeocodeDataIfNeeded(
                $activity,
                $changes,
                $mapboxService
            );
        }
        $subActivity->save();

        foreach ($calendarActivities as $calendarActivity) {
            if ($calendarActivity->start >= $editedCalendarActivityStart) {
                $calendarActivity->activity_id = $subActivity->id;
                if ($timeDifference) {
                    $calendarActivity->start = $calendarActivity->start->add(
                        $timeDifference
                    );
                }
                $calendarActivity->save();
            }
        }

        return $subActivity;
    }
}
