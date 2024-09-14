<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "from" => "date",
        "to" => "date",
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
     * Validation rules for creating a new journey.
     * TODO: Add validation rules for the invite field
     * This is because the invite is only required when creating a new journey.
     */
    public function rules(): array
    {
        return [
            "name" => "required|string",
            "destination" => "required|string",
            "from" => "required|date",
            "to" => "required|date",
            "mapbox_id" => "nullable|string",
            "mapbox_full_address" => "nullable|string",
        ];
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
}
