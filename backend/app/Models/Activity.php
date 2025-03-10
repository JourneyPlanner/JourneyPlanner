<?php

namespace App\Models;

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Activity extends Model
{
    use HasFactory, HasUuids, BroadcastsEvents;

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
        foreach ($this->attributes as $key => $value) {
            if ($key != "id") {
                if ($this->$key != $other->$key) {
                    return false;
                }
            }
        }

        return true;
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

    /**
     * Mutator for setting the repeat_on attribute.
     */
    public function setRepeatOnAttribute($value)
    {
        $this->attributes["repeat_on"] = is_array($value)
            ? implode(",", $value)
            : $value;
    }

    /**
     * Accessor for getting the repeat_on attribute as an array.
     */
    public function getRepeatOnAttribute($value)
    {
        return explode(",", $value);
    }

    /**
     * Get the channels that model events should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model>
     */
    public function broadcastOn(string $event): array
    {
        return [$this->journey];
    }

    public function broadcastWith()
    {
        $array = $this->toArray();
        unset($array["journey"]);
        return $array;
    }
}
