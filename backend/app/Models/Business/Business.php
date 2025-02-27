<?php

namespace App\Models\Business;

use App\Models\Activity;
use App\Models\Journey;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ["name", "slug", "default_language"];

    /**
     * The business's images.
     */
    public function images(): HasMany
    {
        return $this->hasMany(BusinessImage::class);
    }

    /**
     * The business's texts.
     */
    public function texts(): HasMany
    {
        return $this->hasMany(BusinessText::class);
    }

    /**
     * The business's templates.
     */
    public function templates(): BelongsToMany
    {
        return $this->belongsToMany(
            Journey::class,
            "business_templates",
            relatedPivotKey: "template_id"
        );
    }

    /**
     * The business's activities.
     */
    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class, "business_activities");
    }

    /**
     * The users that are a part of the business.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
