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

        if (!$journey->isTemplate) {
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

        return response()->json([
            "average_rating" => $templateRating
                ->template()
                ->get("average_rating"),
        ]);
    }
}
