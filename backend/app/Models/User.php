<?php

namespace App\Models;

use App\Models\Business\Business;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use ProtoneMedia\LaravelVerifyNewEmail\MustVerifyNewEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, MustVerifyNewEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ["display_name", "username", "email", "password"];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ["password", "remember_token", "pivot"];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "email_verified_at" => "datetime",
        "password" => "hashed",
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            if ($user->isDirty("email")) {
                $user->email_hash = hash("sha256", $user->email);
            }
        });
    }

    /**
     * The journeys that the user is a part of.
     */
    public function journeys(): BelongsToMany
    {
        return $this->belongsToMany(Journey::class);
    }

    /**
     * The files that the user has uploaded.
     */
    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    /**
     * The businesses that the user is a part of.
     */
    public function businesses(): BelongsToMany
    {
        return $this->belongsToMany(Business::class);
    }

    /**
     * Generate a unique username based on the display name.
     */
    public static function generateUsername(string $displayName): string
    {
        $username = Str::slug($displayName, "_");
        while (User::where("username", $username)->exists()) {
            $username .= Str::lower(Str::random(4));
        }
        return $username;
    }
}
