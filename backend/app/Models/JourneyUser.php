<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JourneyUser extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = "journey_user";

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["role"];
}
