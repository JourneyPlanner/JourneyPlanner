<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\JourneyUser;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TemplateController extends Controller
{
    private $columns;
    private int $perPage = 20;

    public function __construct()
    {
        $this->columns = [
            "id",
            "name",
            "destination",
            "from",
            "to",
            "description",
            "mapbox_full_address",
            DB::raw("DATEDIFF(`to`, `from`) + 1 AS length"), // Only works with MySQL
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
     * Display all templates created by the user.
     */
    public function userTemplatesIndex(string $username)
    {
        return $this->getTemplates($username);
    }

    /**
     * Get templates based on the provided filters.
     */
    private function getTemplates(string $username = null)
    {
        // Validate the request
        $validated = request()->validate([
            "sort_by" => "nullable|string|in:id,name,destination,length",
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
        ]);

        // Get the validated values or use the default values
        $sortBy = $validated["sort_by"] ?? "id";
        $order = $validated["order"] ?? "asc";
        $perPage = $validated["per_page"] ?? $this->perPage;

        $name = $validated["template_name"] ?? null;
        $lengthMin = $validated["template_journey_length_min"] ?? null;
        $lengthMax = $validated["template_journey_length_max"] ?? null;
        $lengthMaxConst =
            $validated["template_journey_length_max_const"] ?? null;
        $destination = $validated["template_destination_input"] ?? null;
        $destinationName = $validated["template_destination_name"] ?? null;
        $creator = $validated["template_creator"] ?? null;

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
            ->with([
                "users" => function ($query) {
                    $query->select("id", "username", "display_name");
                },
            ])
            ->orderBy($sortBy, $order)
            ->cursorPaginate($perPage, $this->columns)
            ->withQueryString();

        return response()->json($templates);
    }

    /**
     * Store a new template from the journey.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "journey_id" => "required|uuid",
            "name" => "required|string",
            "description" => "string",
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
            ->replicate([
                "name",
                "description",
                "invite",
                "is_template",
                "created_from",
            ])
            ->fill([
                "name" => $validated["name"],
                "invite" => "",
                "description" => $validated["description"] ?? "",
                "is_template" => true,
                "created_from" => $journey->id,
            ]);
        $journeyTemplate->save();

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

        $journeyTemplate->users()->attach(Auth::id(), [
            "role" => JourneyUser::TEMPLATE_CREATOR_ROLE_ID,
        ]);

        return response()->json($journeyTemplate);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Journey $journey)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Journey $journey)
    {
        //
    }
}
