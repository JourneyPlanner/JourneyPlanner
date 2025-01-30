<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Models\Journey;
use App\Models\TemplateRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplateRatingController extends Controller
{
    /**
     * Store, update or delete a new rating for a template.
     */
    public function rate(Journey $journey, Request $request)
    {
        $validated = $request->validate([
            "rating" => "required|integer|min:0|max:5",
        ]);

        if (!$journey->is_template) {
            abort(403, "Journeys cannot be rated.");
        }

        if (
            !TemplateRating::where("template_id", $journey->id)
                ->where("user_id", Auth::id())
                ->exists()
        ) {
            $templateRating = new TemplateRating([
                "user_id" => Auth::id(),
                "template_id" => $journey->id,
                "rating" => $validated["rating"],
            ]);
            $templateRating->save();
        } else {
            $templateRating = TemplateRating::where("template_id", $journey->id)
                ->where("user_id", Auth::id())
                ->first();
            if ($validated["rating"] === 0) {
                $templateRating->delete();
            } else {
                $templateRating->update(["rating" => $validated["rating"]]);
            }
        }

        $template = $templateRating
            ->template()
            ->first(["average_rating", "total_ratings"]);

        return response()->json([
            "average_rating" => $template->average_rating ?? 0,
            "total_ratings" => $template->total_ratings ?? 0,
        ]);
    }

    /**
     * Get a single rating.
     */
    public function show(Journey $journey)
    {
        return response()->json([
            "average_rating" => $journey->average_rating ?? 0,
            "total_ratings" => $journey->total_ratings ?? 0,
        ]);
    }
}
