<?php

namespace App\Http\Controllers;

use App\Models\Journey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "journey_id" => "required|uuid",
            "name" => "required|string",
            "description" => "string",
        ]);
        $journey = Journey::findOrFail($validated["journey_id"]);

        if (
            Journey::where([
                ["is_template", "=", "1"],
                ["created_from", "=", $journey->id],
            ])->exists()
        ) {
            return response()->json(
                [
                    "message" =>
                        "A template has already been created from this journey.",
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
     * Display the specified resource.
     */
    public function show(Journey $journey)
    {
        //
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
