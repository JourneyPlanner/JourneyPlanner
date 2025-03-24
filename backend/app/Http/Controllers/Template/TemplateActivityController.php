<?php

namespace App\Http\Controllers\Template;

use App\Http\Controllers\Journey\Activity\ActivityController;
use App\Models\Journey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\ResponseField;
use Knuckles\Scribe\Attributes\ResponseFromFile;

/**
 * @group Template
 *
 * @subgroup TemplateActivity
 *
 * @subgroupDescription APIs for managing template activities.
 */
class TemplateActivityController extends ActivityController
{
    /**
     * Get all activities for a template.
     */
    #[QueryParam('journey_id', description: 'The ID of the template to get activities for.')]
    #[ResponseFromFile('storage/responses/journey/activity/index.200.json', status: 200, description: 'Success.')]
    #[ResponseFromFile('storage/responses/journey/activity/index.404.json', status: 404, description: 'Template not found.')]
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
        return parent::index($journey, $request);
    }
}
