<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TemplateController extends Controller
{
    private array $columns = [
        "id",
        "name",
        "destination",
        "from",
        "to",
        "description"
    ];

    private int $perPage = 20;

    /**
     * Display all available templates.
     */
    public function index()
    {
        $templates = Journey::where("is_template", true)
            ->with(["users" => function ($query) {
                $query->select("id", "username");
            }])
            ->cursorPaginate(
                $this->perPage,
                $this->columns
            )->withQueryString();

        foreach ($templates as $template) {
            $from = Carbon::parse($template->from);
            $to = Carbon::parse($template->to);
            $template->days = $from->diffInDays($to) + 1;
        }

        return response()->json($templates);
    }

    /**
     * Display all templates created by the user.
     */
    public function userTemplatesIndex(string $username)
    {
        $user = User::where("username", $username)->firstOrFail();
        return response()->json(
            $user
                ->journeys()
                ->where("is_template", true)
                ->cursorPaginate(
                    $this->perPage,
                    $this->columns
                )
        );
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

        $journeyTemplate->users()->attach(auth()->id(), ["role" => 2]); // 2 is the role for the creator of the template

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
