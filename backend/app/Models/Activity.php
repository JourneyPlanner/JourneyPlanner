<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        "name",
        "estimated_duration",
        "mapbox_id",
        "mapbox_full_address",
        "address",
        "opening_hours",
        "email",
        "phone",
        "link",
        "cost",
        "description",
        "location",
        "repeat_type",
        "repeat_interval",
        "repeat_interval_unit",
        "repeat_on",
        "repeat_end_date",
        "repeat_end_occurrences",
    ];

    /**
     * The journey that the activity belongs to.
     */
    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    /**
     * The calendar activity that the activity belongs to.
     */
    public function calendarActivities(): HasMany
    {
        return $this->hasMany(CalendarActivity::class);
    }

    /**
     * The parent activity that the activity belongs to.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Activity::class, "parent_id");
    }

    /**
     * Get the base activity of the activity.
     */
    public function getBaseActivity(): Activity
    {
        return $this->parent()->first()
            ? $this->parent()->first()->getBaseActivity()
            : $this;
    }

    /**
     * The children activities that the activity belongs to.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Activity::class, "parent_id");
    }

    /**
     * Check if the activity has the same attributes as another activity.
     */
    public function hasSameAttributesAs(Activity $other): bool
    {
        return $this->name === $other->name &&
            $this->estimated_duration == $other->estimated_duration &&
            $this->opening_hours === $other->opening_hours &&
            $this->email === $other->email &&
            $this->phone === $other->phone &&
            $this->link === $other->link &&
            $this->cost === $other->cost &&
            $this->description === $other->description &&
            $this->mapbox_id === $other->mapbox_id &&
            $this->mapbox_full_address === $other->mapbox_full_address &&
            $this->address === $other->address &&
            $this->longitude === $other->longitude &&
            $this->latitude === $other->latitude &&
            $this->repeat_type === $other->repeat_type &&
            $this->repeat_interval === $other->repeat_interval &&
            $this->repeat_interval_unit === $other->repeat_interval_unit &&
            $this->repeat_on === $other->repeat_on &&
            $this->repeat_end_date === $other->repeat_end_date &&
            $this->repeat_end_occurrences === $other->repeat_end_occurrences;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        "estimated_duration" => "datetime:H:i:s",
        "repeat_end_date" => "datetime",
    ];
}
