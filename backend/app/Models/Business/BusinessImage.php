<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessImage extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['key', 'file_name', 'business_id'];

    /**
     * The business image's alt texts.
     */
    public function imageAltTexts(): HasMany
    {
        return $this->hasMany(BusinessImageAltText::class);
    }

    /**
     * The business that owns the image.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the path to the image file.
     */
    public function getPath(): string
    {
        return storage_path("app/{$this->file_name}");
    }

    /**
     * Get the URL to the image file.
     */
    public function getLink(): string
    {
        return url(
            "/api/business/{$this->business->slug}/image/{$this->id}",
            [],
            config('app.env') === 'production'
        );
    }
}
