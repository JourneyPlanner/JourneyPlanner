<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\User;
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
            "template_journey_length_max" => "nullable|integer|min:1",
            "template_destination" => "nullable|string",
            "template_creator" => "nullable|string",
        ]);

        // Get the validated values or use the default values
        $sortBy = $validated["sort_by"] ?? "id";
        $order = $validated["order"] ?? "asc";
        $perPage = $validated["per_page"] ?? $this->perPage;

        $name = $validated["template_name"] ?? "";
        $lengthMin = $validated["template_journey_length_min"] ?? 1;
        $lengthMax = $validated["template_journey_length_max"] ?? PHP_INT_MAX;
        $destination = $validated["template_destination"] ?? "";
        $creator = $validated["template_creator"] ?? "";

        // Select all templates that match the search criteria
        $query = Journey::where("is_template", true)
            ->where(function ($query) use ($destination) {
                $query
                    ->where("destination", "like", "%$destination%")
                    ->orWhere("mapbox_full_address", "like", "%$destination%");
            })
            ->where("name", "like", "%$name%")
            ->where(DB::raw("DATEDIFF(`to`, `from`) + 1"), ">=", $lengthMin)
            ->where(DB::raw("DATEDIFF(`to`, `from`) + 1"), "<=", $lengthMax)
            ->with([
                "users" => function ($query) {
                    $query->select("id", "username");
                },
            ]);

        if ($username) {
            $user = User::where("username", $username)->firstOrFail();
            $query->whereHas("users", function ($query) use ($user) {
                $query->where("users.id", $user->id);
            });
        } else {
            $query->whereHas("users", function ($query) use ($creator) {
                $query->where("username", "like", "%$creator%");
            });
        }

        $templates = $query
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

        $journeyTemplate->users()->attach(Auth::id(), ["role" => 2]); // 2 is the role for the creator of the template

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
