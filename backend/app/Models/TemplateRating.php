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
            $rating->updateTemplateRating();
        });

        static::deleted(function ($rating) {
            $rating->updateTemplateRating();
        });
    }

    /**
     * Update the average rating and total ratings of the template.
     */
    public function updateTemplateRating()
    {
        $template = Journey::find($this->template_id);

        $average = TemplateRating::where(
            "template_id",
            $this->template_id
        )->avg("rating");
        $count = TemplateRating::where(
            "template_id",
            $this->template_id
        )->count();

        $globalAverage = TemplateRating::avg("rating");
        $weight = max(5, Journey::avg("total_ratings")); // Confidence value (weight constant)

        if ($count > 0) {
            $bayesianAverage =
                ($average * $count + $weight * $globalAverage) /
                ($count + $weight);
        } else {
            $bayesianAverage = $globalAverage; // If no ratings, default to global average
        }

        $template->update([
            "average_rating" => $bayesianAverage,
            "total_ratings" => $count,
        ]);
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
