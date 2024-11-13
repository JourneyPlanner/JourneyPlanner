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
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        "estimated_duration" => "datetime:H:i:s",
    ];
}
