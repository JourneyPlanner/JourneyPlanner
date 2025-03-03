<?php

namespace App\Models;

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JourneyUser extends Model
{
    use HasFactory, BroadcastsEvents;

    public const JOURNEY_MEMBER_ROLE_ID = 0;
    public const JOURNEY_GUIDE_ROLE_ID = 1;
    public const TEMPLATE_CREATOR_ROLE_ID = 2;

    /**
     * The table associated with the model.
     */
    protected $table = "journey_user";

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["role"];

    /**
     * Get the channels that model events should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel|\Illuminate\Database\Eloquent\Model>
     */
    public function broadcastOn(string $event): array
    {
        return [$this->journey];
    }
}
