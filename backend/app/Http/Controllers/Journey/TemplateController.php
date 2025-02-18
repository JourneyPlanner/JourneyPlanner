<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journey\UpdateTemplateRequest;
use App\Models\Journey;
use App\Models\JourneyUser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TemplateController extends Controller
{
    public static int $perPage = 20;

    /**
     * The columns to exclude when cloning a journey for creating/updating a template.
     */
    private static array $columnsToExcludeFromClone = [
        "id",
        "created_at",
        "updated_at",
        "name",
        "description",
        "invite",
        "is_template",
        "created_from",
        "average_rating",
        "total_ratings",
    ];

    public static function getColumns()
    {
        return [
            "id",
            "name",
            "destination",
            "from",
            "to",
            "description",
            "mapbox_full_address",
            "average_rating",
            "total_ratings",
            "length",
        ];
    }

    /**
     * Display all available templates.
     */
    public function index()
    {
        return $this->getTemplates();
    }

    /**
     * Show the requested template.
     */
    public function show(Journey $journey)
    {
        // Check if the requested journey is a template
        Gate::authorize("journeyTemplate", $journey);

        // Load the template creator
        $journey->load("users:id,display_name,username");

        return response()->json($journey);
    }

    /**
     * Display all templates created by the user.
     */
    public function userTemplatesIndex(string $username)
    {
        return $this->getTemplates($username);
    }

    /**
     * Get all templates by the current user.
     *
     */
    public function currentUserTemplatesIndex()
    {
        return $this->getTemplates(Auth::user()->username);
    }

    /**
     * Get templates based on the provided filters.
     */
    private function getTemplates(string $username = null)
    {
        // Validate the request
        $validated = request()->validate([
            "sort_by" =>
                "nullable|string|in:id,name,destination,length,average_rating",
            "filter_by_rating" => "nullable|integer|min:0|max:5",
            "order" => "nullable|string|in:asc,desc",
            "per_page" => "nullable|integer|min:1|max:100",
            "template_name" => "nullable|string",
            "template_journey_length_min" => "nullable|integer|min:1",
            "template_journey_length_max" => [
                "nullable",
                "integer",
                "min:1",
                function (string $attribute, mixed $value, Closure $fail) {
                    $minLength = request()->input(
                        "template_journey_length_min"
                    );
                    if ($minLength && $value < $minLength) {
                        $fail(
                            "The maximum length must be greater than or equal to the minimum length."
                        );
                    }
                },
            ],
            "template_journey_length_max_const" =>
                "required_with:template_journey_length_max|min:1",
            "template_destination_input" => "nullable|string",
            "template_destination_name" => "nullable|string",
            "template_creator" => "nullable|string",
            "journey_id" => "nullable|uuid|exists:journeys,id",
        ]);

        // Get the validated values or use the default values
        $sortBy = $validated["sort_by"] ?? "average_rating";
        $order = $validated["order"] ?? "desc";
        $perPage = $validated["per_page"] ?? static::$perPage;
        $filterByRating = $validated["filter_by_rating"] ?? null;

        $name = $validated["template_name"] ?? null;
        $lengthMin = $validated["template_journey_length_min"] ?? null;
        $lengthMax = $validated["template_journey_length_max"] ?? null;
        $lengthMaxConst =
            $validated["template_journey_length_max_const"] ?? null;
        $destination = $validated["template_destination_input"] ?? null;
        $destinationName = $validated["template_destination_name"] ?? null;
        $creator = $validated["template_creator"] ?? null;
        $journey_id = $validated["journey_id"] ?? null;

        // Select all templates that match the search criteria
        $templates = Journey::where("is_template", true)
            ->when($destination, function ($query) use (
                $destination,
                $destinationName
            ) {
                $query->where(function ($query) use (
                    $destination,
                    $destinationName
                ) {
                    $query
                        ->where("destination", "like", "%$destination%")
                        ->orWhere(
                            "mapbox_full_address",
                            "like",
                            "%$destination%"
                        )
                        ->when($destinationName, function ($query) use (
                            $destinationName
                        ) {
                            $query
                                ->orWhere(
                                    "destination",
                                    "like",
                                    "%$destinationName%"
                                )
                                ->orWhere(
                                    "mapbox_full_address",
                                    "like",
                                    "%$destinationName%"
                                );
                        });
                });
            })
            ->when($name, function ($query) use ($name) {
                $query->where("name", "like", "%$name%");
            })
            ->when($lengthMin, function ($query) use ($lengthMin) {
                $query->where(
                    DB::raw("DATEDIFF(`to`, `from`) + 1"),
                    ">=",
                    $lengthMin
                );
            })
            ->when($lengthMax, function ($query) use (
                $lengthMax,
                $lengthMaxConst
            ) {
                if ($lengthMax >= $lengthMaxConst) {
                    $lengthMax = PHP_INT_MAX;
                }
                $query->where(
                    DB::raw("DATEDIFF(`to`, `from`) + 1"),
                    "<=",
                    $lengthMax
                );
            })
            ->when(
                $username,
                function ($query) use ($username) {
                    $user = User::where("username", $username)->firstOrFail();
                    $query->whereHas("users", function ($query) use ($user) {
                        $query->where("users.id", $user->id);
                    });
                },
                function ($query) use ($creator) {
                    $query->when($creator, function ($query) use ($creator) {
                        $query->whereHas("users", function ($query) use (
                            $creator
                        ) {
                            $query->where("username", "like", "%$creator%");
                        });
                    });
                }
            )
            ->when($filterByRating, function ($query) use ($filterByRating) {
                $query->where("average_rating", ">=", $filterByRating);
            })
            ->when($journey_id, function ($query) use ($journey_id) {
                $query->where("created_from", $journey_id);
            })
            ->with([
                "users" => function ($query) {
                    $query->select("id", "username", "display_name");
                },
            ])
            ->orderBy($sortBy, $order)
            ->cursorPaginate($perPage, static::getColumns())
            ->withQueryString();

        return response()->json($templates);
    }

    /**
     * Store a new template from the journey.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "journey_id" => "required|uuid|exists:journeys,id",
            "name" => "required|string",
            "description" => "nullable|string",
        ]);
        $journey = Journey::findOrFail($validated["journey_id"]);
        Gate::authorize("update", [$journey, false]);

        if ($request->user()->can("journeyTemplateCreator", $journey)) {
            return response()->json(
                [
                    "message" =>
                        "You have already created a template from this journey.",
                ],
                409
            );
        }

        $journeyTemplate = $journey
            ->replicate(static::$columnsToExcludeFromClone)
            ->fill([
                "name" => $validated["name"],
                "invite" => "",
                "description" => $validated["description"] ?? "",
                "is_template" => true,
                "created_from" => $journey->id,
            ]);
        $journeyTemplate->save();

        $journeyTemplate = $this->cloneActivities($journey, $journeyTemplate);

        $journeyTemplate->users()->attach(Auth::id(), [
            "role" => JourneyUser::TEMPLATE_CREATOR_ROLE_ID,
        ]);

        return response()->json($journeyTemplate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTemplateRequest $request, Journey $journey)
    {
        $validated = $request->validated();
        $template = $journey;

        if (isset($validated["journey_id"])) {
            $journey = Journey::findOrFail($validated["journey_id"]);
            Gate::authorize("update", [$journey, false]);

            $template->fill(
                Arr::except(
                    $journey->toArray(),
                    static::$columnsToExcludeFromClone
                )
            );

            $template->activities()->delete();
            $template = $this->cloneActivities($journey, $template);
        }
        $template->update($validated);

        return response()->json(
            [
                "message" => "Template updated successfully",
                "journey" => $template,
            ],
            200
        );
    }

    /**
     * Clone the activities from the journey to the template.
     */
    private function cloneActivities($journey, $journeyTemplate)
    {
        foreach ($journey->activities()->get() as $activity) {
            $activityTemplate = $activity->replicate(["journey_id"]);
            $activityTemplate->journey_id = $journeyTemplate->id;
            $activityTemplate->save();

            foreach (
                $activity->calendarActivities()->get()
                as $calendarActivity
            ) {
                $calendarActivityTemplate = $calendarActivity
                    ->replicate(["activity_id"])
                    ->fill([
                        "activity_id" => $activityTemplate->id,
                    ]);
                $calendarActivityTemplate->save();
            }
        }

        return $journeyTemplate;
    }
}
