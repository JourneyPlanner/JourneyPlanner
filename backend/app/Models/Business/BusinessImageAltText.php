<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessImageAltText extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['alt_text', 'language', 'business_image_id'];

    /**
     * The image that this alt text belongs to.
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(BusinessImage::class);
    }
}
