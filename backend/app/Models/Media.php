<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = "journey_media";

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["path"];

    /**
     * Get the user that owns the media.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the journey that the media belongs to.
     */
    public function journey(): BelongsTo
    {
        return $this->belongsTo(Journey::class);
    }

    /**
     * Get the media path.
     */
    public function getMediaPath(): string
    {
        return $this->getBasePath() . $this->journey()->first()->id . "/" . $this->name;
    }

    /**
     * Get the media subpath.
     */
    public function getMediaSubpath(): string
    {
        return $this->getSubfolder() . $this->journey()->id . "/" . $this->name;
    }

    /**
     * Get the thumbnail path.
     */
    public function getThumbnailPath(): string
    {
        return $this->getMediaPath() . "_thumbnail.jpg";
    }

    /**
     * Get the journey folder.
     */
    public static function getJourneyFolder(string $journeyId): string
    {
        return Media::getBasePath() . $journeyId;
    }

    /**
     * Get the base path for media.
     */
    public static function getBasePath(): string
    {
        return storage_path("app/" . Media::getSubfolder());
    }

    /**
     * Get the subfolder for media of the default disk.
     */
    public static function getSubfolder(): string
    {
        return "journey_media/";
    }
}
