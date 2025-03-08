<?php

namespace App\Models;

use App\Models\Business\Business;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Journey extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "destination",
        "from",
        "to",
        "invite",
        "mapbox_id",
        "mapbox_full_address",
        "description",
        "is_template",
        "created_from",
        "average_rating",
        "total_ratings",
        "longitude",
        "latitude",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "from" => "datetime",
        "to" => "datetime",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ["pivot"];

    /**
     * The users that are a part of the journey.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The activities that are a part of the journey.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * The uploads that are a part of the journey.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * Get the media folder path.
     */
    public function getMediaFolder(): string
    {
        return Media::getJourneyFolder($this->id);
    }

    /**
     * The templates that are created from this journey.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Journey::class, "created_from")->where(
            "is_template",
            "1"
        );
    }

    /**
     * The businesses that are a part of the journey.
     */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(
            Business::class,
            "business_templates",
            "business_id",
            "journey_id"
        );
    }

    /**
     * Get the journey that the template was created from.
     */
    public function createdFrom(): BelongsTo
    {
        return $this->belongsTo(Journey::class, "created_from");
    }
}
