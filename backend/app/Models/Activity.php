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
        "address",
        "mapbox_id",
        "opening_hours",
        "contact",
        "link",
        "cost",
        "description",
        "location",
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
}
