<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateRating extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["user_id", "template_id", "rating"];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saved(function ($rating) {
            $rating->updateAllTemplateRatings();
        });

        static::deleted(function ($rating) {
            $rating->updateAllTemplateRatings();
        });
    }

    /**
     * Update the average rating and total ratings of the template.
     */
    public function updateTemplateRating($template_id)
    {
        $template = Journey::find($template_id);

        $average = TemplateRating::where("template_id", $template_id)->avg(
            "rating"
        );
        $count = TemplateRating::where("template_id", $template_id)->count();

        $globalAverage = TemplateRating::avg("rating");
        $weight = max(5, Journey::avg("total_ratings")); // Confidence value (weight constant)

        if ($count > 0) {
            $bayesianAverage =
                ($average * $count + $weight * $globalAverage) /
                ($count + $weight);
            if (
                $template
                    ->businesses()
                    ->wherePivot("created_by_business", true)
                    ->count() > 0
            ) {
                $bayesianAverage = $bayesianAverage * 0.9 + 0.5; // 5 * 0.1 = 0.5; 10% are automatically 5 stars to emsure business templates have a slightly higher rating
            }
        } else {
            $bayesianAverage = 0; // If no ratings, default to 0
        }

        $template->update([
            "average_rating" => $bayesianAverage,
            "total_ratings" => $count,
        ]);
    }

    /**
     * Update the average rating and total ratings of all templates.
     */
    public function updateAllTemplateRatings()
    {
        Journey::chunk(100, function ($templates) {
            foreach ($templates as $template) {
                $this->updateTemplateRating($template->id);
            }
        });
    }

    /**
     * Get the user that rated the template.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the template that was rated.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }
}
